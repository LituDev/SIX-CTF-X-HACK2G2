use serde_derive::Serialize;

#[derive(Serialize, Clone)]
pub struct User {
    pub email: String,
    pub username: String,
    pub password: String,
    pub cooldown: i64,
    pub score: u32,
}

impl User {
    pub fn new(email: String, username: String, password: String) -> Self {
        Self {
            email,
            username,
            password,
            cooldown: 0,
            score: 0,
        }
    }
}
