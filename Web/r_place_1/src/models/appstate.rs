use std::sync::RwLock;
use std::{env, fs};
use std::collections::HashMap;

use actix::Addr;
use chrono::Utc;
use image::{DynamicImage, GenericImageView, ImageBuffer, Rgb};
use lettre::Transport;
use lettre::transport::smtp;
use rand::distributions::Alphanumeric;
use rand::Rng;
use regex::Regex;
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
    #[error("SMTP configuration error")]
    SmtpConfigError,
    #[error("Regex compilation error")]
    RegexCompileError,
    #[error("Error parsing email")]
    EmailParseError,
    #[error("Error creating verification email")]
    EmailCreationError,
    #[error("Error sending verification email: {0}")]
    EmailSendingError(#[from] lettre::transport::smtp::Error),
}

pub struct AppState {
    width: usize,
    height: usize,
    cooldown: u16,
    pixels_color: HashMap<String, Vec<u8>>,
    base_image: DynamicImage,
    palette: Vec<(u8, u8, u8)>,
    png: HashMap<String, Vec<u8>>,
    last_update: HashMap<String, i64>,
    update_cooldown: u16,
    message_updates: HashMap<String, Vec<MessageUpdate>>,
    sessions: HashMap<String, RwLock<Vec<Addr<PlaceWebSocketConnection>>>>,
    email_regex: Regex,
    ubs_regex: Regex,
    extract_id_regex: Regex,
    chall_users: HashMap<u32, String>,
    smtp_user: String,
    mailer: lettre::SmtpTransport,
    url: String,
    flag: String,
}

impl AppState {
    pub fn new(width: usize, height: usize) -> Result<Self, AppStateError> {
        let base_image_path = env::var("BASE_IMAGE")
            .map_err(|_| AppStateError::EnvVarNotSet("BASE_IMAGE".to_string()))?;

        let base_image = image::open(&base_image_path)
            .map_err(|_| AppStateError::FileReadError(std::io::Error::new(std::io::ErrorKind::Other, "Error reading image")))?;

        let pixels_color = HashMap::new();

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
            .map(|color| hex_to_rgb(color)).collect();

        let last_update = HashMap::new();
        let png = HashMap::new();
        let message_updates = HashMap::new();
        let sessions = HashMap::new();
        let chall_users = HashMap::new();

        let smtp_server = env::var("SMTP_SERVER")
            .map_err(|_| AppStateError::EnvVarNotSet("SMTP_SERVER".to_string()))?;
        let smtp_port: u16 = env::var("SMTP_PORT")
            .map_err(|_| AppStateError::EnvVarNotSet("SMTP_PORT".to_string()))?
            .parse()
            .map_err(|_| AppStateError::InvalidValueError("SMTP_PORT".to_string()))?;
        let smtp_user = env::var("SMTP_USER")
            .map_err(|_| AppStateError::EnvVarNotSet("SMTP_USER".to_string()))?;
        let smtp_password = env::var("SMTP_PASSWORD")
            .map_err(|_| AppStateError::EnvVarNotSet("SMTP_PASSWORD".to_string()))?;

        let mailer = lettre::SmtpTransport::starttls_relay(&smtp_server)
            .map_err(|_| AppStateError::SmtpConfigError)?
            .port(smtp_port)
            .credentials(smtp::authentication::Credentials::new(smtp_user.clone(), smtp_password))
            .build();

        let email_regex = Regex::new(r"^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$")
            .map_err(|_| AppStateError::RegexCompileError)?;
        let ubs_regex = Regex::new(r"^[a-z0-9.]+@(etud\.)?univ-ubs\.fr$")
            .map_err(|_| AppStateError::RegexCompileError)?;
        let extract_id_regex = Regex::new(r"(?P<id>e\d+)@")
            .map_err(|_| AppStateError::RegexCompileError)?;

        let url = env::var("URL")
            .map_err(|_| AppStateError::EnvVarNotSet("URL".to_string()))?;

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
            email_regex,
            ubs_regex,
            extract_id_regex,
            chall_users,
            smtp_user,
            mailer,
            url,
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

    pub fn add_chall_place(&mut self, ubs_id: u32, email: &str) -> Result<(), AppStateError> {
        if self.chall_users.contains_key(&ubs_id) {
            return Err(AppStateError::InvalidValueError("UBS id already registered".to_string()));
        }

        let chall_code: String = rand::thread_rng()
            .sample_iter(&Alphanumeric)
            .take(16)
            .map(char::from)
            .collect();

        self.pixels_color.insert(chall_code.clone(), vec![31; self.width * self.height]);
        self.last_update.insert(chall_code.clone(), 0);
        self.message_updates.insert(chall_code.clone(), Vec::new());
        self.sessions.insert(chall_code.clone(), RwLock::new(Vec::new()));
        self.chall_users.insert(ubs_id, chall_code.clone());

        self.set_image(&chall_code);
        self.send_chall_url_mail(email, &chall_code)?;

        Ok(())
    }

    pub fn set_image(&mut self, chall_code: &str) {
        let mut pixels_color: Vec<u8> = Vec::new();

        for x in 0..self.width {
            for y in 0..self.height {
                let pixel = self.base_image.get_pixel(x as u32, y as u32);
                let color = (pixel[0], pixel[1], pixel[2]);
                pixels_color.push(self.closest_color(color));
            }
        }

        self.pixels_color.insert(chall_code.to_string(), pixels_color);
    }

    pub fn send_chall_url_mail(&self, email: &str, chall_code: &str) -> Result<(), AppStateError> {
        let parsed_from = self.smtp_user.parse().map_err(|_| AppStateError::EmailParseError)?;
        let parsed_to = email.parse().map_err(|_| AppStateError::EmailParseError)?;

        let email_body = format!(
            "Voici le lien pour accéder a votre instance du chall r/place : {}/{}\n\
            Votre but est de colorier le place en entier en noir (gloire au void).\n\
            Je previens a l'avance tenter de faire ca a la main prendra 45h.\n\
            Vous pourrez accéder au flag ici : {}/{}/flag",
            self.url, chall_code, self.url, chall_code
        );

        let email = lettre::Message::builder()
            .from(parsed_from)
            .to(parsed_to)
            .subject("[CTF] Url chall r/place (1/2)")
            .body(email_body)
            .map_err(|_| AppStateError::EmailCreationError)?;

        self.mailer.send(&email).map_err(|err| AppStateError::EmailSendingError(err))?;
        Ok(())
    }

    pub fn draw(&mut self, x: usize, y: usize, color: u8, chall_code: &str) -> Result<(), AppStateError> {
        let index = x * self.height + y;
        self.pixels_color.get_mut(chall_code)
            .ok_or_else(|| AppStateError::InvalidValueError("Chall instance not found".to_string()))?[index] = color;

        let message_update = MessageUpdate { x, y, color };
        self.message_updates.get_mut(chall_code)
            .ok_or_else(|| AppStateError::InvalidValueError("Chall instance not found".to_string()))?
            .push(message_update.clone());
        self.broadcast(message_update, chall_code)?;

        Ok(())
    }

    pub fn add_session(&self, session: Addr<PlaceWebSocketConnection>, chall_code: &str) -> Result<(), AppStateError> {
        self.sessions.get(chall_code)
            .ok_or_else(|| AppStateError::InvalidValueError("Chall instance not found".to_string()))?
            .write()
            .map(|mut sessions| sessions.push(session))
            .map_err(|_| AppStateError::SessionAddError)
    }

    fn broadcast(&self, msg: MessageUpdate, chall_code: &str) -> Result<(), AppStateError> {
        let sessions = self.sessions.get(chall_code)
            .ok_or_else(|| AppStateError::InvalidValueError("Chall instance not found".to_string()))?
            .read()
            .map_err(|_| AppStateError::SessionAddError)?;
        for session in sessions.iter() {
            session.do_send(msg);
        }
        Ok(())
    }

    pub fn try_update(&mut self, chall_code: &str) -> Result<(), AppStateError> {
        let time = Utc::now().timestamp();
        let last_update = self.last_update.get(chall_code)
            .ok_or_else(|| AppStateError::InvalidValueError("Chall instance not found".to_string()))?;

        if time - last_update < self.update_cooldown as i64 {
            return Ok(());
        }

        let pixels_color = self.pixels_color.get(chall_code)
            .ok_or_else(|| AppStateError::InvalidValueError("Chall instance not found".to_string()))?;

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

        self.png.remove(chall_code);
        self.png.insert(chall_code.to_string(), new_png);

        self.message_updates.remove(chall_code);
        self.message_updates.insert(chall_code.to_string(), Vec::new());
        self.last_update.remove(chall_code);
        self.last_update.insert(chall_code.to_string(), time);

        Ok(())
    }

    pub fn get_flag(&self, chall_code: &str) -> Result<String, AppStateError> {
        let pixel_color = self.pixels_color.get(chall_code)
            .ok_or_else(|| AppStateError::InvalidValueError("Chall instance not found".to_string()))?;

        for i in 0..self.width {
            for j in 0..self.height {
                let index = i * self.height + j;
                if pixel_color[index] != 27 {
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

    pub fn get_png(&self, chall_code: &str) -> Result<Vec<u8>, AppStateError> {
        self.png.get(chall_code)
            .ok_or_else(|| AppStateError::InvalidValueError("Chall instance not found".to_string()))
            .map(|png| png.clone())
    }

    pub fn get_message_updates(&self, chall_code: &str) -> Result<Vec<MessageUpdate>, AppStateError> {
        self.message_updates.get(chall_code)
            .ok_or_else(|| AppStateError::InvalidValueError("Chall instance not found".to_string()))
            .map(|updates| updates.clone())
    }

    pub fn chall_exists(&self, chall_code: &str) -> bool {
        self.pixels_color.contains_key(chall_code)
    }

    pub fn email_regex(&self) -> &Regex {
        &self.email_regex
    }

    pub fn ubs_regex(&self) -> &Regex {
        &self.ubs_regex
    }

    pub fn extract_id_regex(&self) -> &Regex {
        &self.extract_id_regex
    }
}
