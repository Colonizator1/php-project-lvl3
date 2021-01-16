require('./bootstrap');
const ujs = require('@rails/ujs');
ujs.start();
Echo.channel(`domain_check_update_channel`)
    .listen('DomainCheckUpdated', (e) => {
        console.log(e);
        let content = e.domainCheckData;
        let messageBox = document.getElementsByClassName('alert alert-info alert-important').item(0);
        console.log(messageBox);
        messageBox.innerHTML = 'Done!'
        
        if (content.status === 'failed') {
            let peddingCell = document.getElementById('pending-' + e.domainCheckData.id);
            peddingCell.textContent = "Сheck failed! Can\'t connect for 10 sec";
            peddingCell.removeAttribute("class");
            peddingCell.classList.add('alert');
            peddingCell.classList.add('alert-danger');
            messageBox.classList.replace('alert-info', 'alert-danger');
            return;
        }
        messageBox.classList.replace('alert-info', 'alert-success');
        let peddingCell = document.getElementById('pending-' + e.domainCheckData.id);

        peddingCell.textContent = content['status_code'] ? `${content['status_code']}` : '';
        peddingCell.removeAttribute("colspan");
        peddingCell.removeAttribute("class");

        let h1Cell = document.createElement('td');
        h1Cell.textContent = content['h1'] ? `${content['h1']}` : '';
        peddingCell.insertAdjacentElement('afterend', h1Cell);

        let keywordsCell = document.createElement('td');
        keywordsCell.textContent = content['keywords'] ? `${content['keywords']}` : '';
        h1Cell.insertAdjacentElement('afterend', keywordsCell);

        let descriptionCell = document.createElement('td');
        descriptionCell.textContent = content['description'] ? `${content['description']}` : '';
        keywordsCell.insertAdjacentElement('afterend', descriptionCell);

        let updatedAtCell = document.getElementById('updated_at-' + e.domainCheckData.id);
        updatedAtCell.innerHTML = content['updated_at'] ? `${content['updated_at']}` : '';

        
        
    });