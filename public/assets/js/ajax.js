function deleteFunction(id) {
    console.log(id);
    fetch('http://localhost:8030/user/friendRequests', {
        method: 'DELETE'
    })
    .then((response) => response.text())
    .then((text) => console.log(text))
    .then(_ => location.replace("http://localhost:8030/user/friendRequests"));
}