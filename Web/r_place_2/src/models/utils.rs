use serde_derive::{Deserialize, Serialize};

#[derive(Serialize, Deserialize)]
pub struct ColorFile {
    pub colors: Vec<String>,
}

pub fn hex_to_rgb(hex: &str) -> (u8, u8, u8) {
    let hex = hex.trim_start_matches('#');
    let r = u8::from_str_radix(&hex[0..2], 16).unwrap();
    let g = u8::from_str_radix(&hex[2..4], 16).unwrap();
    let b = u8::from_str_radix(&hex[4..6], 16).unwrap();

    (r, g, b)
}
