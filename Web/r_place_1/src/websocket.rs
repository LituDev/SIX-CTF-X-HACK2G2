use std::sync::RwLock;

use actix::{Actor, ActorContext, AsyncContext, Handler, Message, StreamHandler};
use actix_web::{get, web, HttpRequest, HttpResponse, error, Error};
use actix_web_actors::ws;
use serde_derive::Serialize;

use crate::models::appstate::AppState;

#[derive(Message, Clone, Copy, Serialize)]
#[rtype(result = "()")]
pub struct MessageUpdate {
    pub x: usize,
    pub y: usize,
    pub color: u8,
}

pub struct PlaceWebSocketConnection {
    appstate: web::Data<RwLock<AppState>>,
}

impl Actor for PlaceWebSocketConnection {
    type Context = ws::WebsocketContext<Self>;

    fn started(&mut self, ctx: &mut Self::Context) {
        self.appstate.write()
            .map_err(|_| eprintln!("Error writing to app state"))
            .and_then(|appstate| {
                appstate.add_session(ctx.address())
                    .map_err(|err| eprintln!("Error adding session: {}", err))
            })
            .unwrap_or_else(|_| ctx.stop());
    }
}

impl Handler<MessageUpdate> for PlaceWebSocketConnection {
    type Result = ();

    fn handle(&mut self, msg: MessageUpdate, ctx: &mut Self::Context) {
        serde_json::to_string(&msg)
            .map_err(|_| ctx.text("Error serializing update message"))
            .map(|text| ctx.text(text))
            .unwrap_or(());
    }
}

impl StreamHandler<Result<ws::Message, ws::ProtocolError>> for PlaceWebSocketConnection {
    fn handle(&mut self, msg: Result<ws::Message, ws::ProtocolError>, ctx: &mut Self::Context) {
        match msg {
            Ok(ws::Message::Ping(msg)) => ctx.pong(&msg),
            Ok(ws::Message::Text(text)) => ctx.text(text),
            Ok(ws::Message::Binary(bin)) => ctx.binary(bin),
            _ => (),
        }
    }
}

#[get("/api/ws")]
async fn ws_index(
    req: HttpRequest,
    stream: web::Payload,
    data: web::Data<RwLock<AppState>>,
) -> Result<HttpResponse, Error> {
    ws::start(PlaceWebSocketConnection { appstate: data }, &req, stream)
        .map_err(|_| error::ErrorInternalServerError("websocket error"))
}
