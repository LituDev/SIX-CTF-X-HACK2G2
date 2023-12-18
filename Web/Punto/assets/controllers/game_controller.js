import { Controller } from "@hotwired/stimulus"
import {addToast} from "../toasts";
import {refreshUser, addCard, setNextCard, message } from "../game";

import WS from '../../vendor/gos/web-socket-bundle/public/js/websocket.min.js';

export default class extends Controller {
  session = null;

  initialize() {
    var webSocket = WS.connect('ws://'+document.domain+':8080');
    var thisClass = this;

    webSocket.on('socket/connect', async function (sess) {
      if(document.querySelectorAll('.table-overlay .no-remove').length == 0){
        message(null)
      }
      thisClass.session = sess;
      //session is an AutobahnJS WAMP session.

      console.log('Successfully connected!');

      await thisClass.session.subscribe('party/channel', async function (uri, payload) {
        switch (payload.action) {
          case "join":
            addToast(payload.payload.player.name + " joined the party", "success");
            refreshUser(thisClass.session);
            break;
          case "disconnect":
            addToast(payload.payload.player.name + " left the party", "danger");
            refreshUser(thisClass.session);
            break;
          case "addCard":
            console.log(payload);
            addCard(thisClass.session, payload.payload.cell);
            if(!document.body.classList.contains("game-over")) {
              addToast(payload.payload.player.name + " added a card, " + payload.payload.next.player.name + " is next", "success");
              setNextCard(payload.payload.nextCard)
              refreshUser(thisClass.session);
            }
            break;
          case "start":
            document.getElementById("start-button").style.display = "none";
            console.log(payload);
            message(null)
            document.querySelectorAll("td").forEach(function (cell) {
              cell.innerHTML = "+";
            });
            addToast("The game has started", "success");
            await refreshUser(thisClass.session);
            setNextCard(payload.payload.nextCard)
            break;
          case "win":
            addToast(payload.payload.winner.name + " won the round", "success");
            message(payload.payload.winner.name + " won the round")
            document.body.classList.add("game-over");
            document.getElementById("round-button").style.display = "block";
            break;
          case "flag":
            addToast("You got the flag!", "success");
            message("You got the flag!");
            alert(payload.payload.flag);
            break;
          case "end":
            addToast(payload.payload.winner.name + " won the game", "success");
            message(payload.payload.winner.name + " won the game")
            document.body.classList.add("game-over");
            document.getElementById("round-button").style.display = "none";
            break;
        }

        console.log('Received a message for subscription', uri, payload);

      });

      refreshUser(thisClass.session).then(() => {
        thisClass.session.call('board/get').then(
          function (result) {
            console.log("cells", result.result.cells)
            setNextCard(result.result.nextCard);
            result.result.cells.forEach(function (cell) {
              addCard(thisClass.session, cell);
            });
          },
          function (error, desc) {
            console.log('RPC Error', error, desc);
          }
        );
      });
    });

    webSocket.on('socket/disconnect', function (error) {
      //error provides us with some insight into the disconnection: error.reason and error.code

      message("Reconnecting...")
      console.log('Disconnected for ' + error.reason + ' with code ' + error.code);
    });
  }

  addCard(event) {
    event.preventDefault();
    this.session.call('partyrpc/addCard', {x: event.params.x, z: event.params.z}).then(
      function (result) {
        if(result.error){
          addToast(result.error, "danger");
        }
        console.log('RPC Result', result);
      },
      function (error, desc) {
        console.log('RPC Error', error, desc);
      }
    );
  }

  start(event){
    event.preventDefault();
    this.session.call('partyrpc/start').then(
      function (result) {
        if(result.error){
          addToast(result.error, "danger");
        }
      },
      function (error, desc) {
        console.log('RPC Error', error, desc);
      }
    );
  }

  round(event){
    event.preventDefault();
    this.session.call('partyrpc/start').then(
      function (result) {
        if(result.error){
          addToast(result.error, "danger");
        }
      },
      function (error, desc) {
        console.log('RPC Error', error, desc);
      }
    );
  }
}
