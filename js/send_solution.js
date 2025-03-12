google.accounts.id.initialize({
    client_id: "466834063559-e8ntnvvptcbbdp70ovb3v1m4h8qm3c8i.apps.googleusercontent.com",
    callback: onSignIn,
    auto_select: true,
    cancel_on_tap_outside: false
});

function send() {
    google.accounts.id.prompt();
};

function onSignIn(user) {
    document.cookie = `token=${user.credential}; expires=${new Date(parseJwt(user.credential)["exp"] * 1000).toUTCString()};`;
    document.getElementById('send_form').submit();
}

function parseJwt(token) {
    var base64Url = token.split('.')[1];
    var base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    var jsonPayload = decodeURIComponent(window.atob(base64).split('').map(function (c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
    }).join(''));

    return JSON.parse(jsonPayload);
}