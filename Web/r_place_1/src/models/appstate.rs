use std::sync::RwLock;
use std::{env, fs};

use actix::Addr;
use chrono::Utc;
use image::{DynamicImage, GenericImageView, ImageBuffer, Rgb};
use thiserror::Error;

use crate::models::utils::{hex_to_rgb, ColorFile};
use crate::websocket::{MessageUpdate, PlaceWebSocketConnection};

#[derive(Error, Debug)]
pub enum AppStateError {
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
    base_image: DynamicImage,
    palette: Vec<(u8, u8, u8)>,
    png: Vec<u8>,
    last_update: i64,
    update_cooldown: u16,
    message_updates: Vec<MessageUpdate>,
    sessions: RwLock<Vec<Addr<PlaceWebSocketConnection>>>,
    flag: String,
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
            .map_err(|_| AppStateError::InvalidValueError("COOLDOWN_SEC".to_string()))?;

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

        let pixels_color = Vec::new();
        let last_update = 0;
        let png = Vec::new();
        let message_updates = Vec::new();
        let sessions = RwLock::new(Vec::new());

        let flag = env::var("FLAG")
            .map_err(|_| AppStateError::EnvVarNotSet("FLAG".to_string()))?;

        Ok(Self {
            width,
            height,
            cooldown,
            pixels_color,
            base_image,
            palette,
            last_update,
            update_cooldown,
            message_updates,
            sessions,
            png,
            flag,
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

    pub fn draw(&mut self, x: usize, y: usize, color: u8) -> Result<(), AppStateError> {
        if x >= self.width || y >= self.height {
            return Err(AppStateError::InvalidValueError("x or y out of bounds".to_string()));
        }

        let index = x * self.height + y;
        self.pixels_color[index] = color;

        let message_update = MessageUpdate { x, y, color };
        self.message_updates.push(message_update.clone());
        self.broadcast(message_update)?;

        Ok(())
    }

    pub fn add_session(&self, session: Addr<PlaceWebSocketConnection>) -> Result<(), AppStateError> {
        self.sessions
            .write()
            .map(|mut sessions| sessions.push(session))
            .map_err(|_| AppStateError::SessionAddError)
    }

    fn broadcast(&self, msg: MessageUpdate) -> Result<(), AppStateError> {
        let sessions = self.sessions
            .read()
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

        let pixels_color = self.pixels_color.clone();

        let image = ImageBuffer::from_fn(self.width as u32, self.height as u32, |x, y| {
            let index = (x as usize) * self.height + (y as usize);
            let color = self.palette[pixels_color[index] as usize];
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
}
