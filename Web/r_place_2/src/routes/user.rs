use std::sync::RwLock;

use actix_web::{get, post, web, HttpRequest, HttpResponse, Error, error};
use chrono::{Duration, Utc};
use jsonwebtoken::{encode, Algorithm, EncodingKey, Header};
use serde_derive::Deserialize;

use crate::models::appstate::AppState;
use crate::models::user::User;
use crate::routes::utils::{token_to_id, Claims};

#[derive(Deserialize)]
struct LoginInfo {
    username: String,
    password: String,
}

#[derive(Deserialize)]
struct SignupInfo {
    username: String,
    password: String,
    email: String,
}

#[derive(Deserialize)]
pub struct ProfileEdit {
    pub username: String,
    pub password: String,
    pub current_password: String,
}

#[post("/api/login")]
async fn login(
    appstate: web::Data<RwLock<AppState>>,
    info: web::Json<LoginInfo>,
) -> Result<HttpResponse, Error> {
    let appstate = appstate
        .write()
        .map_err(|_| error::ErrorInternalServerError("appstate read error"))?;

    let user_id = appstate
        .get_user_id(&info.username)
        .ok_or_else(|| error::ErrorBadRequest("User not found"))?;

    let user = appstate
        .get_user(user_id)
        .ok_or_else(|| error::ErrorBadRequest("User not found"))?;

    if user.password != info.password {
        return Err(error::ErrorBadRequest("Invalid credentials"));
    }

    let claims = Claims {
        id: user_id,
        exp: (Utc::now() + Duration::days(7)).timestamp() as usize,
    };

    let token = encode(
        &Header::new(Algorithm::HS512),
        &claims,
        &EncodingKey::from_secret(appstate.jwt_secret().as_bytes()),
    )
        .map_err(|_| error::ErrorInternalServerError("token encoding error"))?;

    Ok(HttpResponse::Ok().body(token))
}

#[post("/api/signup")]
async fn signup(
    appstate: web::Data<RwLock<AppState>>,
    info: web::Json<SignupInfo>,
) -> Result<HttpResponse, Error> {
    if info.username.len() < 3 || info.username.len() > 15 {
        return Err(error::ErrorBadRequest("username must be between 3 and 15 characters"));
    }

    if info.password.len() < 8 || info.password.len() > 128 {
        return Err(error::ErrorBadRequest("password must be between 8 and 128 characters"));
    }

    let mut appstate = appstate
        .write()
        .map_err(|_| error::ErrorInternalServerError("appstate write error"))?;

    if !appstate.email_regex().is_match(&info.email) {
        return Err(error::ErrorBadRequest("Invalid email format"));
    }

    match appstate.email_exists(&info.email) {
        true => return Err(error::ErrorBadRequest("Email already registered")),
        false => (),
    }

    match appstate.is_username_taken(&info.username) {
        true => return Err(error::ErrorBadRequest("Username taken")),
        false => (),
    }

    let user = User::new(
        info.email.clone(),
        info.username.clone(),
        info.password.clone(),
    );

    appstate.insert_user(user)
        .map_err(|err| error::ErrorInternalServerError(format!("appstate error: {}", err)))?;

    Ok(HttpResponse::Ok().body("ok"))
}

#[get("/api/profile/me")]
async fn get_profile(
    appstate: web::Data<RwLock<AppState>>,
    req: HttpRequest,
) -> Result<HttpResponse, Error> {
    let appstate = appstate.read()
        .map_err(|_| error::ErrorInternalServerError("appstate read error"))?;

    let user_id = token_to_id(req, appstate.jwt_secret().as_bytes())?;
    let user = appstate.get_user(user_id)
        .ok_or_else(|| error::ErrorBadRequest("invalid user"))?;

    Ok(HttpResponse::Ok().json(user))
}

#[post("/api/profile/edit")]
async fn edit_profile(
    appstate: web::Data<RwLock<AppState>>,
    info: web::Json<ProfileEdit>,
    req: HttpRequest,
) -> Result<HttpResponse, Error> {
    let mut appstate = appstate
        .write()
        .map_err(|_| error::ErrorInternalServerError("appstate write error"))?;

    let user_id = token_to_id(req, appstate.jwt_secret().as_bytes())?;

    if info.username.len() < 3 || info.username.len() > 15 {
        return Err(error::ErrorBadRequest("username must be between 3 and 15 characters"));
    }

    if info.password.len() < 8 || info.password.len() > 128 {
        return Err(error::ErrorBadRequest("password must be between 8 and 128 characters"));
    }

    let user = appstate
        .get_user(user_id)
        .ok_or_else(|| error::ErrorBadRequest("invalid user"))?;

    let is_username_taken = appstate.is_username_taken(&info.username);

    if user.username != info.username && is_username_taken {
        return Err(error::ErrorBadRequest("username taken"));
    }

    if user.password != info.current_password {
        return Err(error::ErrorBadRequest("invalid password"));
    }

    let user = appstate
        .get_user_mut(user_id)
        .ok_or_else(|| error::ErrorBadRequest("invalid user"))?;

    user.username = info.username.clone();
    user.password = info.password.clone();

    Ok(HttpResponse::Ok().body("ok"))
}

#[get("/api/users/count")]
async fn get_user_count(
    appstate: web::Data<RwLock<AppState>>,
) -> Result<HttpResponse, Error> {
    let appstate = appstate.read()
        .map_err(|_| error::ErrorInternalServerError("appstate read error"))?;

    Ok(HttpResponse::Ok().json(appstate.user_count()))
}