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
        profileTitle.innerText = '';

        let profileUuid = document.getElementById('profile-uuid');
        profileUuid.innerText = '';

        let historyTable = document.getElementById('history-table');
        historyTable.innerHTML = '<tr><th>Name</th><th>Date Changed</th></tr>';
        /* END Text Resets */

        // Used to 'live-update' the omni-bar with our current query
        let url = new URL(window.location);
        url.searchParams.set('q', form.q.value);
        window.history.pushState({}, '', url);

        // Send a request to the our profile resolver script
        window.fetch('getprofile.php', {
            method: 'POST',
            headers: {
                'content-type': 'application/x-www-form-urlencoded'
            },
            body: `q=${escape(form.q.value)}`
        }).then(res => res.json().then(profile => {
            if (profile.error) { // Uh oh
                displayError(profile.error, false);
            } else {
                document.title = `${profile.names[0].name} | open-mc`; // Set title to '%NAME% | open-mc'

                profileTitle.innerText = `${profile.names[0].name}:`;
                profileUuid.innerHTML = `<strong>UUID:</strong> ${profile.uuid}`;

                // Iterate through all the history entries we retrieved
                for (let entry of profile.names) {
                    let tr = document.createElement('tr');

                    let nameTd = document.createElement('td');
                    nameTd.className = 'history-name';
                    nameTd.innerHTML = `<a href="?q=${entry.name}">${entry.name}</a>`;

                    let dateTd = document.createElement('td');
                    dateTd.className = 'history-date';
                    dateTd.innerHTML = 'Original';
                    if (entry.changedToAt) {
                        dateTd.innerHTML = `${formatDate(new Date(entry.changedToAt))}`;
                    }

                    tr.appendChild(nameTd);
                    tr.appendChild(dateTd);
                    historyTable.append(tr);
                }
            }
        }));

        form.q.select();
        if (event != null) {
            event.preventDefault();
        }
        return false;
    };

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

        let meridiem = date.getHours() < 12 ? 'AM' : 'PM';

        return `${date.getDate()}/${month}/${date.getFullYear()} ${hours}:${date.getMinutes()}:${date.getSeconds()} ${meridiem}`;
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