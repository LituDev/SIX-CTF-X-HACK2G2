use std::sync::RwLock;

use actix_web::{post, web, HttpResponse, Error, error, get};
use serde_derive::Deserialize;

use crate::models::appstate::AppState;

#[derive(Deserialize)]
struct EmailInfo {
    email: String,
}

#[post("/newinstance")]
async fn new_instance(
    appstate: web::Data<RwLock<AppState>>,
    info: web::Json<EmailInfo>,
) -> Result<HttpResponse, Error> {
    let mut appstate = appstate.write()
        .map_err(|err| error::ErrorInternalServerError(format!("appstate error: {}", err)))?;

    if !appstate.email_regex().is_match(&info.email) {
        return Err(error::ErrorBadRequest("Invalid email format"));
    }

    if !appstate.ubs_regex().is_match(&info.email) {
        return Err(error::ErrorBadRequest("Not a UBS email"));
    }

    let ubs_id = appstate
        .extract_id_regex()
        .captures(&info.email)
        .and_then(|captures| captures.get(1))
        .and_then(|capture| {
            let id_str = &capture.as_str()[1..];
            id_str.parse::<u32>().ok()
        })
        .ok_or_else(|| error::ErrorBadRequest("Invalid email format"))?;

    appstate.add_chall_place(ubs_id, &info.email)
        .map_err(|err| error::ErrorInternalServerError(format!("appstate error: {}", err)))?;

    Ok(HttpResponse::Ok().json("Code sent"))
}

#[get("/{chall_code}/flag")]
async fn get_flag(
    appstate: web::Data<RwLock<AppState>>,
    path: web::Path<(String,)>,
) -> Result<HttpResponse, Error> {
    let (chall_code,) = path.into_inner();
    let appstate = appstate.read()
        .map_err(|_| error::ErrorInternalServerError("appstate read error"))?;

    if !appstate.chall_exists(&chall_code) {
        return Ok(HttpResponse::NotFound().body("Chall instance not found"));
    }

    let flag = appstate.get_flag(&chall_code)
        .map_err(|_| error::ErrorUnauthorized("Challenge not solved"))?;

    Ok(HttpResponse::Ok().json(flag))
}
