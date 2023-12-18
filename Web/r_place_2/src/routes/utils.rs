use actix_web::{Error, error, HttpRequest};
use jsonwebtoken::{decode, Algorithm, DecodingKey, Validation};
use serde_derive::{Deserialize, Serialize};

#[derive(Deserialize, Serialize)]
pub struct Claims {
    pub id: u16,
    pub exp: usize,
}

pub fn token_to_id(req: HttpRequest, key: &[u8]) -> Result<u16, Error> {
    let header = req.headers().get("Authorization")
        .ok_or_else(|| error::ErrorUnauthorized("no token"))?;

    let header_str = header.to_str().map_err(|_| error::ErrorUnauthorized("token header error"))?;

    if !header_str.starts_with("Bearer ") {
        return Err(error::ErrorUnauthorized("not bearer token"));
    }

    let token = header_str.trim_start_matches("Bearer ");

    decode::<Claims>(
        token,
        &DecodingKey::from_secret(key),
        &Validation::new(Algorithm::HS512),
    )
        .map(|data| data.claims.id)
        .map_err(|_| error::ErrorUnauthorized("invalid token"))
}
