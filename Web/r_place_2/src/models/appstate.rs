use std::collections::HashMap;
use std::sync::RwLock;
use std::{env, fs};

use actix::Addr;
use chrono::Utc;
use image::{DynamicImage, GenericImageView, ImageBuffer, Rgb};
use regex::Regex;
use thiserror::Error;

use crate::models::user::User;
use crate::models::utils::{hex_to_rgb, ColorFile};
use crate::websocket::{MessageUpdate, PlaceWebSocketConnection};

#[derive(Error, Debug)]
pub enum AppStateError {
    #[error("Regex compilation error")]
    RegexCompileError,
    #[error("File read error: {0}")]
    FileReadError(#[from] std::io::Error),
    #[error("JSON deserialization error: {0}")]
    JsonParseError(#[from] serde_json::Error),
    #[error("Environment variable not set: {0}")]
    EnvVarNotSet(String),
    #[error("Invalid value: {0}")]
    InvalidValueError(String),
    #[error("Error adding session")]
    SessionAddError,
}

pub struct AppState {
    width: usize,
    height: usize,
    cooldown: u16,
    pixels_color: Vec<u8>,
    pixels_user: Vec<u16>,
    base_image: DynamicImage,
    palette: Vec<(u8, u8, u8)>,
    users: HashMap<u16, User>,
    png: Vec<u8>,
    last_update: i64,
    update_cooldown: u16,
    message_updates: Vec<MessageUpdate>,
    sessions:RwLock<Vec<Addr<PlaceWebSocketConnection>>>,
    jwt_secret: String,
    flag: String,
    email_regex: Regex,
}

impl AppState {
    pub fn new(width: usize, height: usize) -> Result<Self, AppStateError> {
        let base_image_path = env::var("BASE_IMAGE")
            .map_err(|_| AppStateError::EnvVarNotSet("BASE_IMAGE".to_string()))?;

        let base_image = image::open(&base_image_path)
            .map_err(|_| AppStateError::FileReadError(std::io::Error::new(std::io::ErrorKind::Other, "Error reading image")))?;

        let cooldown = env::var("COOLDOWN_SEC")
            .map_err(|_| AppStateError::EnvVarNotSet("COOLDOWN_SEC".to_string()))?
            .parse::<u16>()
            .map_err(|_| AppStateError::InvalidValueError("COOLDOWN".to_string()))?;

        let update_cooldown = env::var("UPDATE_COOLDOWN_SEC")
            .map_err(|_| AppStateError::EnvVarNotSet("UPDATE_COOLDOWN_SEC".to_string()))?
            .parse::<u16>()
            .map_err(|_| AppStateError::InvalidValueError("UPDATE_COOLDOWN_SEC".to_string()))?;

        let colors_str = fs::read_to_string("public/misc/colors.json")
            .map_err(AppStateError::FileReadError)?;
        let color_file = serde_json::from_str::<ColorFile>(&colors_str)
            .map_err(AppStateError::JsonParseError)?;
        let palette: Vec<(u8, u8, u8)> = color_file.colors
            .iter()
            .map(|color| hex_to_rgb(color))
            .collect();

        let email_regex = Regex::new(r"^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$")
            .map_err(|_| AppStateError::RegexCompileError)?;

        let pixels_color = vec![31; width * height];
        let pixels_user = vec![0; width * height];
        let users = HashMap::new();
        let last_update = 0;
        let png = Vec::new();
        let message_updates = Vec::new();
        let sessions = RwLock::new(Vec::new());

        let jwt_secret = env::var("JWT_SECRET")
            .map_err(|_| AppStateError::EnvVarNotSet("JWT_SECRET".to_string()))?;

        let flag = env::var("FLAG")
            .map_err(|_| AppStateError::EnvVarNotSet("FLAG".to_string()))?;

        Ok(Self {
            width,
            height,
            pixels_color,
            pixels_user,
            base_image,
            palette,
            users,
            last_update,
            update_cooldown,
            message_updates,
            sessions,
            cooldown,
            jwt_secret,
            png,
            flag,
            email_regex,
        })
    }

    fn closest_color(&self, color: (u8, u8, u8)) -> u8 {
        let mut min_dist = 255 * 255 * 3;
        let mut min_index = 0;
        for (i, palette_color) in self.palette.iter().enumerate() {
            let dist = (color.0 as i32 - palette_color.0 as i32).pow(2) +
                (color.1 as i32 - palette_color.1 as i32).pow(2) +
                (color.2 as i32 - palette_color.2 as i32).pow(2);
            if dist < min_dist {
                min_dist = dist;
                min_index = i;
            }
        }
        min_index as u8
    }

    pub fn set_image(&mut self) {
        self.pixels_color.clear();
        for x in 0..self.width {
            for y in 0..self.height {
                let pixel = self.base_image.get_pixel(x as u32, y as u32);
                let color = (pixel[0], pixel[1], pixel[2]);
                self.pixels_color.push(self.closest_color(color));
            }
        }
    }

    pub fn draw(&mut self, x: usize, y: usize, user_id: u16, color: u8) -> Result<(), AppStateError> {
        if x >= self.width || y >= self.height {
            return Err(AppStateError::InvalidValueError("x or y out of bounds".to_string()));
        }

        let index = x * self.height + y;
        self.pixels_color[index] = color;
        self.pixels_user[index] = user_id;
        self.users.get_mut(&user_id)
            .ok_or_else(|| AppStateError::InvalidValueError("User not found".to_string()))?
            .score += 1;

        let message_update = MessageUpdate { x, y, color };
        self.message_updates.push(message_update.clone());
        self.broadcast(message_update)?;

        Ok(())
    }

    pub fn add_session(&self, session: Addr<PlaceWebSocketConnection>) -> Result<(), AppStateError> {
        self.sessions.write()
            .map(|mut sessions| sessions.push(session))
            .map_err(|_| AppStateError::SessionAddError)
    }

    fn broadcast(&self, msg: MessageUpdate) -> Result<(), AppStateError> {
        let sessions = self.sessions.read()
            .map_err(|_| AppStateError::SessionAddError)?;
        for session in sessions.iter() {
            session.do_send(msg);
        }
        Ok(())
    }

    pub fn try_update(&mut self) -> Result<(), AppStateError> {
        let time = Utc::now().timestamp();

        if time - self.last_update < self.update_cooldown as i64 {
            return Ok(());
        }

        let image = ImageBuffer::from_fn(self.width as u32, self.height as u32, |x, y| {
            let index = (x as usize) * self.height + (y as usize);
            let color = self.palette[self.pixels_color[index] as usize];
            Rgb([color.0, color.1, color.2])
        });

        let mut new_png: Vec<u8> = Vec::new();
        {
            let mut cursor = std::io::Cursor::new(&mut new_png);
            image.write_to(&mut cursor, image::ImageOutputFormat::Png)
                .map_err(|_| AppStateError::FileReadError(std::io::Error::new(std::io::ErrorKind::Other, "Error writing image")))?;
        }

        self.png = new_png;

        self.message_updates.clear();
        self.last_update = time;

        Ok(())
    }

    pub fn get_flag(&self) -> Result<String, AppStateError> {
        for i in 0..self.width {
            for j in 0..self.height {
                let index = i * self.height + j;
                if self.pixels_color[index] != 27 {
                    return Err(AppStateError::InvalidValueError("Chall not completed".to_string()));
                }
            }
        }

        Ok(self.flag.clone())
    }

    pub fn get_size(&self) -> (usize, usize) {
        (self.width, self.height)
    }

    pub fn get_cooldown(&self) -> u16 {
        self.cooldown
    }

    pub fn get_png(&self) -> Vec<u8> {
        self.png.clone()
    }

    pub fn get_message_updates(&self) -> Vec<MessageUpdate> {
        self.message_updates.clone()
    }

    pub fn get_user(&self, id: u16) -> Option<&User> {
        self.users.get(&id)
    }

    pub fn get_user_id(&self, username: &str) -> Option<u16> {
        for (id, user) in self.users.iter() {
            if user.username == username {
                return Some(*id);
            }
        }
        None
    }

    pub fn get_user_mut(&mut self, id: u16) -> Option<&mut User> {
        self.users.get_mut(&id)
    }

    pub fn email_exists(&self, email: &str) -> bool {
        self.users
            .values()
            .any(|user| user.email == email)
    }

    pub fn insert_user(&mut self, user: User) -> Result<(), AppStateError> {
        let id = self.users.len() as u16 + 1;

        self.users.insert(id, user);

        Ok(())
    }

    pub fn get_leaderboard(&self) -> Vec<User> {
        let mut users = self.users.clone();

        let mut users: Vec<User> = users.drain().map(|(_, user)| user).collect();

        users.sort_by(|a, b| a.score.cmp(&b.score));
        users.into_iter().take(10).collect()
    }

    pub fn is_username_taken(&self, username: &str) -> bool {
        self.users
            .values()
            .any(|user| user.username == username)
    }

    pub fn get_username_from_pixel(&self, x: usize, y: usize) -> String {
        if x >= self.width || y >= self.height {
            return "Invalid Coordinates".to_string();
        }

        let index = x * self.height + y;

        let user_id = self.pixels_user[index];

        match self.users.get(&user_id)
        {
            Some(user) => user.username.clone(),
            None => "No Username".to_string()
        }
    }

    pub fn user_count(&self) -> usize {
        self.users.len()
    }

    pub fn email_regex(&self) -> &Regex {
        &self.email_regex
    }

    pub fn jwt_secret(&self) -> &str {
        &self.jwt_secret
    }
}
