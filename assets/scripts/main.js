(function () {
    'use strict';

    window.initPage = () => {
        // This might not be necessary but I'm not taking chances.
        document.getElementById('title-link').href = window.location.pathname;

        // Attempt to detect a on-load query that is already present
        let query = getQuery();
        if (query.length > 0) {
            let form = document.forms[0];
            form.q.value = unescape(query['q']);

            getProfile(form, null);
        }
    };

    // Queries the profile resolver 
    window.getProfile = (form, event) => {
        /* BEGIN Text Resets */
        document.title = 'open-mc';

        displayError('', true);

        let profileTitle = document.getElementById('profile-title');

        let infoTable = document.getElementById('info-table');
        infoTable.innerHTML = '<tr><th>Information</th></tr>';

        let historyTable = document.getElementById('history-table');
        historyTable.innerHTML = '<tr><th>Names</th></tr>';
        /* END Text Resets */

        // Send a request to the our profile resolver script
        window.fetch('resolver.php', {
            method: 'POST',
            headers: {
                'content-type': 'application/x-www-form-urlencoded'
            },
            body: `q=${escape(form.q.value)}`
        }).then(res => res.json().then(profile => {
            if (profile.error) { // Uh oh
                displayError(profile.error, false);
            } else {
                document.title = `${profile.name} | open-mc`; // Set title to '%NAME% | open-mc'

                profileTitle.innerText = profile.name;

                let uuidTr = document.createElement('tr');
                uuidTr.className = 'info-entry';

                let uuidTextTd = document.createElement('td');
                uuidTextTd.style.width = '1px';
                uuidTextTd.innerHTML = '<strong>UUID</strong>';

                let uuidTd = document.createElement('td');
                uuidTd.className = 'info-uuid';
                uuidTd.innerHTML = `${profile.fullUuid}<br />${profile.uuid}`;

                uuidTr.appendChild(uuidTextTd);
                uuidTr.appendChild(uuidTd);
                infoTable.appendChild(uuidTr);

                let capesTr = document.createElement('tr');
                capesTr.className = 'info-entry';

                let capesTextTd = document.createElement('td');
                capesTextTd.style.width = '1px';
                capesTextTd.innerHTML = '<strong>Capes</strong>';

                let capesTd = document.createElement('td');
                capesTd.className = 'info-capes';
                // capesTd.innerHTML = `Mojang: ${profile.mojangCapes.length > 0}<br />OptiFine: ${profile.ofCape}`;
                capesTd.innerHTML = `Mojang: (not implemented)<br />OptiFine: <a href="http://s.optifine.net/capes/${profile.name}.png">${profile.ofCape}</a>`;

                capesTr.appendChild(capesTextTd);
                capesTr.appendChild(capesTd);
                infoTable.appendChild(capesTr);

                // Iterate through all the history entries we retrieved
                for (let i = 0; i < profile.history.length; i++) {
                    let entry = profile.history[i];

                    let historyTr = document.createElement('tr');
                    historyTr.className = 'history-entry';

                    let idTd = document.createElement('td');
                    idTd.className = 'history-id';
                    idTd.innerText = `${profile.history.length - i}`;

                    let nameTd = document.createElement('td');
                    nameTd.className = 'history-name';
                    nameTd.innerHTML = `<a class="name-anchor" href="?q=${entry.name}">${entry.name}</a>`;
                    if (i == 0) {
                        nameTd.innerHTML += ' (current)';
                    }

                    let dateTd = document.createElement('td');
                    dateTd.className = 'history-date';
                    dateTd.innerHTML = 'Original';
                    if (entry.changedToAt) {
                        dateTd.innerHTML = `${formatDate(new Date(entry.changedToAt))}`;
                    }

                    historyTr.appendChild(idTd);
                    historyTr.appendChild(nameTd);
                    historyTr.appendChild(dateTd);
                    historyTable.append(historyTr);
                }
            }
        }));

        if (event != null) { // This was called using the actual form, not from a URL bar query
            form.q.select();
            event.preventDefault();

            // Update the URL-bar with our current query
            let url = new URL(window.location);
            url.searchParams.set('q', form.q.value);
            window.history.pushState({}, `${form.q.value} | open-mc`, url);
        }
        return false;
    };

    // TODO: Possibly use for quick profile sharing?
    const copyToClipboard = text => {
        let tf = document.createElement('textarea');
        tf.value = text;

        tf.select();
        tf.setSelectionRange(0, text.length);

        document.execCommand('copy');
        tf.remove();
    }

    // Displays an error box, or hides it
    const displayError = (error, hide = false) => {
        let errorBox = document.getElementById('error-box');
        errorBox.innerText = error;

        errorBox.style.visibility = hide ? 'collapse' : 'visible';
    }

    // Formats a Date object into DD/MM/YYYY HH:MM:SS AM/PM
    const formatDate = date => {
        let month = date.getMonth() + 1;

        let hours = (date.getHours() + 12) % 12;
        if (hours === 0) hours = 12;
        if (hours < 10) hours = '0' + hours;

        let minutes = date.getMinutes();
        if (minutes < 10) minutes = '0' + minutes;

        let seconds = date.getSeconds();
        if (seconds < 10) seconds = '0' + seconds;

        let meridiem = date.getHours() < 12 ? 'AM' : 'PM';

        return `${date.getDate()}/${month}/${date.getFullYear()} ${hours}:${minutes}:${seconds} ${meridiem}`;
    };

    // Returns a Key-Value Pair list of all the queries in the URL
    const getQuery = () => {
        let map = [];

        if (document.location.search !== '') {
            let queries = window.location.search.substring(1).split('&');

            for (let query of queries) {
                let data = query.split('=', 2);

                map.push(data[0]);
                map[data[0]] = '';
                if (data.length > 1) {
                    map[data[0]] = data[1];
                }
            }
        }

        return map;
    };
})();