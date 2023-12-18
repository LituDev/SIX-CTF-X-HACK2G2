let menu = document.getElementById('menu');
let menuButton = document.getElementById('menuButton');

let loginDiv = document.getElementById('login');
let loginButton = document.getElementById('loginButton');
let loginLink = document.getElementById('loginLink');
let loginUsername = document.getElementById('loginUsername');
let loginPassword = document.getElementById('loginPassword');
let loginUsernameError = document.getElementById('loginUsernameError');
let loginPasswordError = document.getElementById('loginPasswordError');

let signupDiv = document.getElementById('signup');
let signupButton = document.getElementById('signupButton');
let signupLink = document.getElementById('signupLink');
let signupEmail = document.getElementById('signupEmail');
let signupUsername = document.getElementById('signupUsername');
let signupPassword = document.getElementById('signupPassword');
let signupEmailError = document.getElementById('signupEmailError');
let signupUsernameError = document.getElementById('signupUsernameError');
let signupPasswordError = document.getElementById('signupPasswordError');

let profileDiv = document.getElementById('profile');
let profileReloadButton = document.getElementById('profileReloadButton');
let profilePlacedPixels = document.getElementById('profilePlacedPixels');
let profileUsername = document.getElementById('profileUsername');
let profilePassword = document.getElementById('profilePassword');
let profileCurrentPassword = document.getElementById('profileCurrentPassword');
let profileUsernameError = document.getElementById('profileUsernameError');
let profilePasswordError = document.getElementById('profilePasswordError');
let profileCurrentPasswordError = document.getElementById('profileCurrentPasswordError');
let logoutButton = document.getElementById('logoutButton');
let saveButton = document.getElementById('saveButton');

async function initMenu() {
    setupListeners();
    toggleMenu();
    openLogin();
    await getProfile();
}

function setupListeners() {
    menuButton.addEventListener('click', toggleMenu);
    signupLink.addEventListener('click', openSignup);
    loginLink.addEventListener('click', openLogin);
    loginButton.addEventListener('click', login);
    signupButton.addEventListener('click', signup);
    logoutButton.addEventListener('click', logout);
    saveButton.addEventListener('click', saveProfile);
}

function toggleMenu() {
    menu.style.display = menu.style.display === "none" ? "flex" : "none";
}

function openLogin() {
    loginDiv.style.display = "flex";
    signupDiv.style.display = "none";
    profileDiv.style.display = "none";
}

function openSignup() {
    loginDiv.style.display = "none";
    signupDiv.style.display = "flex";
    profileDiv.style.display = "none";
}

function openProfile() {
    loginDiv.style.display = "none";
    signupDiv.style.display = "none";
    profileDiv.style.display = "flex";
}

function logout() {
    localStorage.removeItem('token');
    openLogin();
    toggleMenu();
    switchState("notConnected");
}

async function saveProfile() {
    let token = localStorage.getItem("token");

    profileUsernameError.textContent = "";

    if(profileUsername.value.length < 3) {
        profileUsernameError.textContent = "Please enter at least 3 characters.";
        return;
    } else if(profileUsername.value.length > 15) {
        profileUsernameError.textContent = "Please enter at most 15 characters.";
        return;
    }

    profileCurrentPasswordError.textContent = "";

    if(!profileCurrentPassword.value) {
        profileCurrentPasswordError.textContent = "Please enter your current password.";
        return;
    }

    profilePasswordError.textContent = "";

    if(profilePassword.value) {
        if(profilePassword.value.length < 8) {
            profilePasswordError.textContent = "Please enter at least 8 characters.";
            return;
        } else if(profilePassword.value.length > 128) {
            profilePasswordError.textContent = "Please enter at most 128 characters.";
            return;
        }
    } else {
        profilePassword.value = "";
    }

    try {
        const response = await fetch(`${window.location.pathname}/api/profile/edit`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({
                username: profileUsername.value,
                password: profilePassword.value,
                current_password: profileCurrentPassword.value
            })
        });

        if (response.ok) {
            getProfile();
            toggleMenu();
            getLeaderboard();
        } else {
            switch(response.status) {
                case 401:
                    profileCurrentPasswordError.textContent = "Invalid password.";
                    break;
                case 409:
                    profileUsernameError.textContent = "Username already taken.";
                    break;
                case 400:
                    profileUsernameError.textContent = "Username must be 3-15 characters long.";
                    profilePasswordError.textContent = "Password must be 8-128 characters long.";
                    break;
            }
        }
    } catch (error) {
        console.error("Error:", error);
    }
}

async function getProfile() {
    profileReloadButton.classList.toggle('rotate')
    profileReloadButton.onanimationend = () => {
        profileReloadButton.classList.toggle('rotate')
    }

    const token = localStorage.getItem("token");

    if (token === null) {
        openLogin();
        switchState("notConnected");
        return;
    }

    try {
        let profileResponse = await fetch(`${window.location.pathname}/api/profile/me`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        let countResponse = await fetch(`${window.location.pathname}/api/users/count`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        if(profileResponse.ok && countResponse.ok) {
            let profile = await profileResponse.json();
            let count = await countResponse.json();
            profilePlacedPixels.value = profile.score;
            profileUsername.value = profile.username;
            profilePassword.value = "";
            profileCurrentPassword.value = "";
            switchState("palette");
            let currentTime = new Date().getTime() / 1000;
            localCooldown = profile.cooldown - currentTime;
            updateCooldownDisplay();
            openProfile();
        } else {
            localStorage.removeItem('token');
            switchState("notConnected");
            openLogin();
            console.error("Error:", await profileResponse.text());
        }
    } catch (error) {
        console.error("Error:", error);
    }
}

async function login() {
    if(loginUsername.value === "") {
        loginUsernameError.textContent = "Please enter an username.";
        return;
    }

    loginUsernameError.textContent = "";

    if(loginPassword.value === "") {
        loginPasswordError.textContent = "Please enter a password.";
        return;
    }

    try {
        const response = await fetch(`${window.location.pathname}/api/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                username: loginUsername.value,
                password: loginPassword.value
            })
        });

        if (response.ok) {
            let token = await response.text();
            localStorage.setItem('token', token);
            await getProfile();
            openProfile();
            toggleMenu();
        } else {
            loginUsernameError.textContent = "Invalid username or password.";
            loginPasswordError.textContent = "Invalid username or password.";
        }
    } catch (error) {
        console.error("Error:", error);
    }
}

async function signup() {
    try {
        signupEmail.value = signupEmail.value.trim();
        const emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;

        if(signupEmail.value === "" || !emailRegex.test(signupEmail.value)) {
            signupEmailError.textContent = "Please enter a valid email address.";
            return;
        }

        signupEmailError.textContent = "";

        signupUsername.value = signupUsername.value.trim();
        if(signupUsername.value.length < 3) {
            signupUsernameError.textContent = "Please enter at least 3 characters.";
            return;
        }
        signupUsernameError.textContent = "";

        signupPassword.value = signupPassword.value.trim();
        if(signupPassword.value.length < 8) {
            signupPasswordError.textContent = "Please enter at least 8 characters.";
            return;
        }
        signupPasswordError.textContent = "";

        const signupResponse = await fetch(`${window.location.pathname}/api/signup`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                email: signupEmail.value,
                username: signupUsername.value,
                password: signupPassword.value
            })
        });

        if (signupResponse.ok) {
            openLogin();
        } else {
            signupEmailError.textContent = "Something went wrong.";
            signupUsernameError.textContent = "Something went wrong.";
            signupPasswordError.textContent = "Something went wrong.";
            console.error("Error:", await signupResponse.text());
        }
    } catch (error) {
        console.error("Error:", error);
    }
}

profileReloadButton.addEventListener('click', getProfile);

document.addEventListener('DOMContentLoaded', () => {
    initMenu();
});