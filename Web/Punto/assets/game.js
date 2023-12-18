async function refreshUser(session) {
  return new Promise((resolve, reject) => {
    session.call("partyrpc/connected").then(
      function (result) {
        let container = document.querySelector("ul#playerConnected");
        let userId = document.querySelector(".game-board").dataset.user;
        container.innerHTML = "";
        let nextPlayer;
        let sorted = [];
        console.log(result)
        result.result.forEach((player) => {
            if(player.position != null){
              sorted[player.position] = player;
            }else{
              sorted.push(player);
            }
        });
        document.querySelectorAll(".player-deck-title").forEach((deck) => {
          deck.classList.add("busy-deck");
        });
        document.querySelectorAll(".player-deck").forEach((deck) => {
          deck.classList.add("busy-deck");
        });
        document.querySelectorAll(".deck-hidden").forEach((deck) => {
          deck.classList.remove("deck-hidden");
        });
        sorted.forEach((player) => {
            let display = "";
            if(result.next == null){
              display = "none";
            }else{
              display = result.next.player.id === player.player.id ? "" : "none";
              nextPlayer = result.next.player;
            }
            let content =  `<li id="player-${player.player.id}">
                ${player.player.name}  `;
            if(player.player.id == userId){
              content += `<span class="player-tag">You</span>`;
            }
            container.innerHTML += content + `
                <span id="player-actual-${player.player.id}" class="actual-tag" style="display:${display}">Actuel</span>
            </li>`;

            try {
              let playerDeck = document.getElementById("player-" + player.position);
              playerDeck.setAttribute("data-user", player.player.id);
              playerDeck.classList.remove("busy-deck");
              let playerTitle = document.getElementById("player-" + player.position + "-title");
              playerTitle.classList.remove("busy-deck");
              playerTitle.innerHTML = player.player.name;
              playerTitle.innerHTML += player.player.id == userId ? `<span class="player-tag">You</span>` : "";
              playerTitle.innerHTML += `<span id="player-actual-${player.player.id}" class="actual-tag" style="display:${display}">Actuel</span>`;
            }catch(err) {
              console.log(err)
            }
        });
        document.querySelectorAll(".busy-deck").forEach((deck) => {
          deck.classList.add("deck-hidden");
          deck.classList.remove("busy-deck");
        });
        document.getElementById("start-button").disabled = result.length < 2;
        if(!document.body.classList.contains("winned")) {
          if (nextPlayer.id != userId) {
            message(nextPlayer.name + " is playing")
          } else {
            message(null)
          }
        }

        resolve(result);
      },
      function (error, desc) {
        console.log('RPC Error', error, desc);
        reject(error);
      }
    );
  }).catch((error) => {
    console.log(error);
  });
}

function addCard(session, cell) {
  console.log("addCard", cell)
  if(cell.cards.length == 0){
    console.log("no card");
    return;
  }
  let x = cell.x;
  let z = cell.z;
  let card = cell.cards[cell.cards.length-1];
  let colorHexa = card.color.toString(16).padStart(6, '0')
  console.log(card.color, colorHexa)
  document.getElementById(x + " " + z).innerHTML = `
    <span style="color: #${colorHexa}">${card.number}</span>
  `;
}

function setNextCard(card){
  let colorHexa = card.color.toString(16).padStart(6, '0')
  document.querySelectorAll(".player-deck").forEach((deck) => {
    deck.style.color = "";
    deck.innerHTML = "  ";
  });
  let playerDeck = document.querySelector(".player-deck[data-user='" + card.player.id + "']");
  playerDeck.innerHTML = card.number;
  playerDeck.style.color = "#" + colorHexa;
}

function message(msg){
  if(msg != null){
    document.getElementById("table-overlay").style.display = "flex";
    document.getElementById("table-overlay").innerHTML = "<p>" + msg + "</p>";
  }else{
    document.getElementById("table-overlay").style.display = "none";
    document.getElementById("table-overlay").innerHTML = "";
  }
}

export { refreshUser, addCard, setNextCard, message };
