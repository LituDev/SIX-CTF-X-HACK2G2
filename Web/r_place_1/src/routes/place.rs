use std::sync::RwLock;

use actix_web::{get, post, web, HttpResponse, Error, error, HttpRequest};
use serde_derive::Deserialize;

use crate::models::appstate::AppState;

#[derive(Deserialize)]
struct DrawInfo {
    x: u32,
    y: u32,
    color: u8,
}

#[get("/api/png")]
async fn get_png(
    appstate: web::Data<RwLock<AppState>>,
) -> Result<HttpResponse, Error> {
    let mut appstate = appstate.write()
        .map_err(|_| error::ErrorInternalServerError("appstate write error"))?;

    appstate.try_update()
        .map_err(|err| eprintln!("appstate error: {}", err)).ok();

    Ok(HttpResponse::Ok().content_type("image/png").body(appstate.get_png()))
}

#[get("/api/updates")]
async fn get_updates(
    appstate: web::Data<RwLock<AppState>>,
) -> Result<HttpResponse, Error> {
    let appstate = appstate.read()
        .map_err(|_| error::ErrorInternalServerError("appstate read error"))?;

    Ok(HttpResponse::Ok().json(appstate.get_message_updates()))
}

#[post("/api/draw")]
async fn draw(
    appstate: web::Data<RwLock<AppState>>,
    info: web::Json<DrawInfo>,
) -> Result<HttpResponse, Error> {
    let mut appstate = appstate.write()
        .map_err(|err| error::ErrorInternalServerError(format!("appstate error: {}", err)))?;

    appstate.draw(info.x as usize, info.y as usize, info.color)
        .map_err(|err| error::ErrorInternalServerError(format!("appstate error: {}", err)))?;

    appstate.try_update()
        .map_err(|err| eprintln!("appstate error: {}", err)).ok();

    Ok(HttpResponse::Ok().json(appstate.get_cooldown()))
}

#[get("/api/size")]
async fn get_size(
    appstate: web::Data<RwLock<AppState>>,
) -> Result<HttpResponse, Error> {
    let appstate = appstate.read()
        .map_err(|_| error::ErrorInternalServerError("appstate read error"))?;

    Ok(HttpResponse::Ok().json(appstate.get_size()))
}

#[get("/flag")]
async fn get_flag(
    appstate: web::Data<RwLock<AppState>>,
) -> Result<HttpResponse, Error> {
    let appstate = appstate.read()
        .map_err(|_| error::ErrorInternalServerError("appstate read error"))?;

    let flag = appstate.get_flag()
        .map_err(|_| error::ErrorUnauthorized("Challenge not solved"))?;

    Ok(HttpResponse::Ok().json(flag))
}