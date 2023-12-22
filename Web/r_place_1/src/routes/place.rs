use std::sync::RwLock;

use actix_web::{get, post, web, HttpResponse, Error, error, HttpRequest};
use serde_derive::Deserialize;

use crate::models::appstate::AppState;
use crate::routes::utils::check_request_header;

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
    check_request_header(&req, &path.0)?;

    let mut appstate = appstate.write()
        .map_err(|err| error::ErrorInternalServerError(format!("appstate error: {}", err)))?;

    if !appstate.chall_exists(&path.0) {
        return Ok(HttpResponse::NotFound().body("Chall instance not found"));
    }

    appstate.draw(info.x as usize, info.y as usize, info.color, &path.0)
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