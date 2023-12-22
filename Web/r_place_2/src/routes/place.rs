use std::sync::RwLock;

use actix_web::{get, post, web, HttpRequest, HttpResponse, Error, error};
use chrono::Utc;
use serde_derive::Deserialize;

use crate::models::appstate::AppState;
use crate::routes::utils::token_to_id;

#[derive(Deserialize)]
struct DrawInfo {
    x: u32,
    y: u32,
    color: u8,
}

#[get("/{chall_code}")]
async fn get_chall_index(
    appstate: web::Data<RwLock<AppState>>,
    path: web::Path<(String,)>,
) -> Result<HttpResponse, Error> {
    let appstate = appstate.write()
        .map_err(|_| error::ErrorInternalServerError("appstate write error"))?;

    if !appstate.chall_exists(&path.0) {
        return Ok(HttpResponse::NotFound().body("Chall instance not found"));
    }

    Ok(HttpResponse::Ok().body(include_str!("../../public/place.html")))
}

#[get("/{chall_code}/api/png")]
async fn get_png(
    appstate: web::Data<RwLock<AppState>>,
    path: web::Path<(String,)>,
) -> Result<HttpResponse, Error> {
    let mut appstate = appstate.write()
        .map_err(|_| error::ErrorInternalServerError("appstate write error"))?;

    if !appstate.chall_exists(&path.0) {
        return Ok(HttpResponse::NotFound().body("Chall instance not found"));
    }

    appstate.try_update(&path.0)
        .map_err(|err| eprintln!("appstate error: {}", err)).ok();

    let png = appstate.get_png(&path.0)
        .map_err(|err| error::ErrorInternalServerError(format!("appstate error: {}", err)))?;

    Ok(HttpResponse::Ok().content_type("image/png").body(png))
}

#[get("/{chall_code}/api/updates")]
async fn get_updates(
    appstate: web::Data<RwLock<AppState>>,
    path: web::Path<(String,)>,
) -> Result<HttpResponse, Error> {
    let appstate = appstate.read()
        .map_err(|_| error::ErrorInternalServerError("appstate read error"))?;

    if !appstate.chall_exists(&path.0) {
        return Ok(HttpResponse::NotFound().body("Chall instance not found"));
    }

    let message_updates = appstate.get_message_updates(&path.0)
        .map_err(|err| error::ErrorInternalServerError(format!("appstate error: {}", err)))?;

    Ok(HttpResponse::Ok().json(message_updates))
}

#[post("/{chall_id}/api/draw")]
async fn draw(
    appstate: web::Data<RwLock<AppState>>,
    info: web::Json<DrawInfo>,
    path: web::Path<(String,)>,
    req: HttpRequest,
) -> Result<HttpResponse, Error> {
    let mut appstate = appstate.write()
        .map_err(|err| error::ErrorInternalServerError(format!("appstate error: {}", err)))?;

    let user_id = token_to_id(req, appstate.jwt_secret().as_bytes())
        .map_err(|_| error::ErrorBadRequest("Failed to decode token"))?;

    let user = appstate.get_user(user_id, &path.0)
        .map_err(|err| error::ErrorInternalServerError(format!("appstate error: {}", err)))?
        .ok_or(error::ErrorBadRequest("invalid user"))?;

    let time = Utc::now().timestamp();

    if info.x >= appstate.get_size().0 as u32 || info.y >= appstate.get_size().1 as u32 {
        return Err(error::ErrorBadRequest("invalid coordinates"));
    }

    if user.cooldown - time > 0 {
        return Err(error::ErrorBadRequest(format!("cooldown not over : {}s", user.cooldown - time)));
    }

    appstate.draw(info.x as usize, info.y as usize, user_id, info.color, &path.0)
        .map_err(|err| error::ErrorInternalServerError(format!("appstate error: {}", err)))?;

    appstate.try_update(&path.0)
        .map_err(|err| eprintln!("appstate error: {}", err)).ok();

    Ok(HttpResponse::Ok().json(appstate.get_cooldown()))
}

#[get("/{chall_code}/api/size")]
async fn get_size(
    appstate: web::Data<RwLock<AppState>>,
    path: web::Path<(String,)>,
) -> Result<HttpResponse, Error> {
    let appstate = appstate.read()
        .map_err(|_| error::ErrorInternalServerError("appstate read error"))?;

    if !appstate.chall_exists(&path.0) {
        return Ok(HttpResponse::NotFound().body("Chall instance not found"));
    }

    Ok(HttpResponse::Ok().json(appstate.get_size()))
}

#[get("/{chall_code}/api/username/{x}/{y}")]
async fn get_username(
    appstate: web::Data<RwLock<AppState>>,
    path: web::Path<(String, u32, u32)>,
) -> Result<HttpResponse, Error> {
    let (chall_code, x, y) = path.into_inner();

    let appstate = appstate.read()
        .map_err(|_| error::ErrorInternalServerError("appstate read error"))?;

    if x >= appstate.get_size().0 as u32 || y >= appstate.get_size().1 as u32 {
        return Err(error::ErrorBadRequest("invalid coordinates"));
    }

    let username = appstate.get_username_from_pixel(x as usize, y as usize, &chall_code)
        .map_err(|err| error::ErrorInternalServerError(format!("appstate error: {}", err)))?;

    Ok(HttpResponse::Ok().body(username))
}

#[get("/{chall_code}/api/leaderboard")]
async fn get_leaderboard(
    appstate: web::Data<RwLock<AppState>>,
    path: web::Path<(String,)>,
) -> Result<HttpResponse, Error> {
    let appstate = appstate.read()
        .map_err(|_| error::ErrorInternalServerError("appstate read error"))?;

    let leaderboard = appstate.get_leaderboard(&path.0)
        .map_err(|err| error::ErrorInternalServerError(format!("appstate error: {}", err)))?;

    Ok(HttpResponse::Ok().json(leaderboard))
}