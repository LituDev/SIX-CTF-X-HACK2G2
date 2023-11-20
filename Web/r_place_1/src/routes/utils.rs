use std::env;
use actix_web::{Error, error, HttpRequest};

pub fn check_request_header(req: &HttpRequest, chall_code: &str) -> Result<(), Error> {
    let user_agent = req.headers().get("User-Agent")
        .ok_or_else(|| error::ErrorBadRequest("Missing User-Agent header"))?
        .to_str()
        .map_err(|_| error::ErrorBadRequest("Invalid User-Agent header"))?;

    let user_agent_min_len = 20;

    if user_agent.len() < user_agent_min_len {
        return Err(error::ErrorBadRequest("Invalid User-Agent header"));
    }

    let referer = req.headers().get("Referer")
        .ok_or_else(|| error::ErrorBadRequest("Missing Referer header"))?
        .to_str()
        .map_err(|_| error::ErrorBadRequest("Invalid Referer header"))?;

    let url = env::var("URL")
        .map_err(|_| error::ErrorInternalServerError("URL env var not set"))?;

    let valid_referer = format!("{}/{}", url, chall_code);

    if referer != valid_referer {
        return Err(error::ErrorBadRequest("Invalid Referer header"));
    }

    Ok(())
}
