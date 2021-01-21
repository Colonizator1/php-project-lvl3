require('./bootstrap');
const ujs = require('@rails/ujs');
ujs.start();
const options = {
    method: 'GET',
};
// repeat every 1 sec
const timerId = setInterval(() => fetch(window.location.href, options)
    .then(async (response) => {
        if (response.status == 200) {
            const htmlFromServer = await response.text();
            const domparser = new DOMParser();
            const docFromServer = domparser.parseFromString(htmlFromServer, 'text/html');
            const rowsFromClient = document.getElementById("checks_table").getElementsByTagName("tr");
            
            let stack = [];
            for (let tr of rowsFromClient) {
                if (tr.dataset.status === 'pending') {
                    stack.push(1);
                    if (docFromServer.getElementById("check-id-" + tr.dataset.id).dataset.status !== 'pending') {
                        const checkedRowFromServer = docFromServer.getElementById("check-id-" + tr.dataset.id);
                        console.log(checkedRowFromServer, 'success/failed row on server');
                        tr.outerHTML = checkedRowFromServer.outerHTML;
                        stack.pop();
                    }
                }
            }
            console.log(stack, 'stack');
            if (stack.length === 0) {
                const messageBox = document.getElementsByClassName('alert alert-info alert-important').item(0);
                if (messageBox) {
                    messageBox.innerHTML = 'Done!';
                }
                clearInterval(timerId);
            }
        }
    })
    .catch(console.error)
, 1000);
