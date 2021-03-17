(function () {
    'use strict';

    window.initPage = () => {
        document.getElementById('title-link').href = window.location.pathname;

        let query = getQuery();
        if (query.length > 0) {
            let form = document.forms[0];
            form.q.value = unescape(query['q']);
            getProfile(form, null);
        }
    };

    window.getProfile = (form, event) => {
        document.title = 'open-mc';

        displayError('', true);

        let profileTitle = document.getElementById('profile-title');
        profileTitle.innerText = '';

        let profileUuid = document.getElementById('profile-uuid');
        profileUuid.innerText = '';

        let historyTable = document.getElementById('history-table');
        historyTable.innerHTML = '<tr><th>Name</th><th>Date Changed</th></tr>';

        let url = new URL(window.location);
        url.searchParams.set('q', form.q.value);
        window.history.pushState({}, '', url);

        window.fetch('getprofile.php', {
            method: 'POST',
            headers: {
                'content-type': 'application/x-www-form-urlencoded'
            },
            body: `q=${escape(form.q.value)}`
        }).then(res => res.json().then(profile => {
            if (profile.error) {
                displayError(profile.error, false);
            } else {
                document.title = `${profile.names[0].name} | open-mc`;

                profileTitle.innerText = `${profile.names[0].name}:`;
                profileUuid.innerHTML = `<strong>UUID:</strong> ${profile.uuid}`;
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

        form.reset();

        if (event != null) {
            event.preventDefault();
        }
        return false;
    };

    const displayError = (error, hide = false) => {
        let errorBox = document.getElementById('error-box');
        errorBox.innerText = error;

        errorBox.style.visibility = hide ? 'collapse' : 'visible';
    }

    const formatDate = date => {
        let month = date.getMonth() + 1;

        let hours = (date.getHours() + 12) % 12;
        if (hours === 0) hours = 12;

        let meridiem = date.getHours() < 12 ? 'AM' : 'PM';

        return `${date.getDate()}/${month}/${date.getFullYear()} ${hours}:${date.getMinutes()}:${date.getSeconds()} ${meridiem}`;
    };

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