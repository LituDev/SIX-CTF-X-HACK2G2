let leaderboard = document.getElementById('leaderboardList');
let reloadButton = document.getElementById('reloadButton');

async function getLeaderboard() {
    reloadButton.classList.toggle('rotate');
    reloadButton.onanimationend = () => {
        reloadButton.classList.toggle('rotate');
    }

    try {
        const response = await fetch('/api/leaderboard');
        if (!response.ok) {
            console.error(`Failed to fetch leaderboard: ${await response.text()}`);
        }
        const data = await response.json();

        leaderboard.innerHTML = '';
        data.forEach(user => {
            let userElement = document.createElement('div');
            userElement.className = 'leaderboard-user';

            let userName = document.createElement('p');
            userName.className = 'leaderboard-name';
            userName.textContent = user.username;

            let userPixels = document.createElement('p');
            userPixels.className = 'leaderboard-pixels';
            userPixels.textContent = user.score;

            userElement.appendChild(userName);
            userElement.appendChild(userPixels);

            leaderboard.appendChild(userElement);
        });

    } catch (error) {
        console.error("Error fetching leaderboard:", error);
    }
}

reloadButton.addEventListener('click', getLeaderboard);

getLeaderboard();