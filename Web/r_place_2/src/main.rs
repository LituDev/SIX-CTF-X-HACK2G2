mod models;
mod routes;
mod websocket;

use std::{env, fs, io};
use std::io::Write;
use std::path::Path;
use std::sync::RwLock;

use actix_cors::Cors;
use actix_files::Files;
use actix_web::{web, App, HttpServer};

use crate::models::appstate::AppState;
use crate::routes::place::{draw, get_chall_index, get_leaderboard, get_png, get_size, get_updates, get_username};
use crate::routes::user::{edit_profile, get_profile, get_user_count, login, signup};
use crate::routes::ctf::{get_flag, new_instance};
use crate::websocket::ws_index;

#[actix_web::main]
async fn main() -> io::Result<()> {
    bundle_js().expect("Error bundling js");

    let width: usize = env::var("WIDTH")
        .expect("WIDTH must be set")
        .parse()
        .expect("WIDTH should be a valid usize");

    let height: usize = env::var("HEIGHT")
        .expect("HEIGHT must be set")
        .parse()
        .expect("HEIGHT should be a valid usize");

    let bind_address = env::var("BIND_ADDRESS").expect("BIND_ADDRESS must be set");

    let port: u16 = env::var("PORT")
        .expect("PORT must be set")
        .parse()
        .expect("PORT should be a valid u16");

    let appstate = web::Data::new(RwLock::new(AppState::new(width, height)
        .expect("Error creating appstate")));

    HttpServer::new(move || {
        let app = App::new()
            .wrap(
                Cors::default()
                    .allow_any_origin()
                    .allow_any_method()
                    .allow_any_header()
                    .max_age(3600),
            )
            .app_data(appstate.clone())
            .service(get_chall_index)
            .service(get_png)
            .service(get_updates)
            .service(draw)
            .service(login)
            .service(signup)
            .service(get_leaderboard)
            .service(ws_index)
            .service(get_size)
            .service(get_profile)
            .service(edit_profile)
            .service(get_username)
            .service(new_instance)
            .service(get_user_count)
            .service(get_flag)
            .service(Files::new("/", "public").index_file("newinstance.html"));

        app
    })
    .bind((bind_address, port))?
    .run()
    .await
}

fn bundle_js() -> io::Result<()> {
    let input_dir = Path::new("public/js/");
    let output_file_path = input_dir.join("bundle.js");

    let mut bundle = fs::File::create(&output_file_path)?;

    for entry in fs::read_dir(input_dir)? {
        let entry = entry?;
        let path = entry.path();
        if path.is_file() && path.extension().map_or(false, |e| e == "js") && path != output_file_path {
            let content = fs::read_to_string(&path)?;
            writeln!(bundle, "// {}\n", path.display())?;
            writeln!(bundle, "{}\n", content)?;
        }
    }

    Ok(())
}