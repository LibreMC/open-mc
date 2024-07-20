(function () {
    'use strict';

    window.onBodyLoad = () => {
        // This might not be necessary but I'm not taking chances.
        document.getElementById('title-link').href = window.location.pathname;

        // Attempt to detect a on-load query that is already present
        let query = getQuery();
        if (query.length > 0) {
            let form = document.forms[0];
            form.q.value = decodeURIComponent(query['q']);

            resolve(form, null);
        }
    };

    // Queries the profile resolver 
    window.resolve = (form, event) => {
        /* BEGIN Text Resets */
        document.title = 'open-mc';

        displayError('', true);

        let profileTitle = document.getElementById('profile-title');

        let infoTable = document.getElementById('info-table');
        infoTable.innerHTML = '<tr><th>Information</th></tr>';

        let historyTable = document.getElementById('history-table');
        historyTable.innerHTML = '<tr><th>Username History</th></tr>';
        /* END Text Resets */

        // Send a request to the our profile resolver script
        window.fetch(`OpenMC/resolver.php?q=${encodeURIComponent(form.q.value)}`)
            .then(res => res.json().then(profile => {
                if (profile.error) { // Uh oh
                    displayError(profile.error, false);
                    return;
                }

                document.title = `${profile.username} | open-mc`;
                profileTitle.innerText = profile.username;

                buildProfileInformation(profile).forEach(row => infoTable.appendChild(row));
                buildHistoryTable(profile.usernameHistory).forEach(row => historyTable.appendChild(row));
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

    const buildProfileInformation = (profile) => {
        // UUID
        let uuidRow = document.createElement('tr');
        uuidRow.className = 'info-entry';

        let uuidCategory = document.createElement('td');
        uuidCategory.style.width = '1px';
        uuidCategory.innerHTML = '<strong>UUID</strong>';

        let uuidEntries = document.createElement('td');
        uuidEntries.className = 'info-uuid';
        uuidEntries.innerHTML = `${profile.fullId}<br />${profile.id}`;

        uuidRow.appendChild(uuidCategory);
        uuidRow.appendChild(uuidEntries);

        // CAPES
        let capesRow = document.createElement('tr');
        capesRow.className = 'info-entry';

        let capesCategory = document.createElement('td');
        capesCategory.style.width = '1px';
        capesCategory.innerHTML = '<strong>Capes</strong>';

        let capeEntries = document.createElement('td');
        capeEntries.className = 'info-capes';
        // capeEntries.innerHTML = `Mojang: ${profile.mojangCapes.length > 0}<br />OptiFine: ${profile.hasOptiFineCape}`;
        capeEntries.innerHTML = `Mojang: (not implemented)<br />OptiFine: <a href="http://s.optifine.net/capes/${profile.username}.png">${profile.hasOptiFineCape}</a>`;

        capesRow.appendChild(capesCategory);
        capesRow.appendChild(capeEntries);

        return [uuidRow, capesRow];
    };

    const buildHistoryTable = (history) => {
        let rows = [];

        for (let i = 0; i < history.length; i++) {
            let historyEntry = history[i];

            let historyRow = document.createElement('tr');
            historyRow.className = 'history-entry';

            let nameIndex = document.createElement('td');
            nameIndex.className = 'history-id';
            nameIndex.innerText = `${history.length - i}`;

            let nameEntry = document.createElement('td');
            nameEntry.className = 'history-name';
            nameEntry.innerHTML = `<a class="name-anchor" href="?q=${historyEntry.name}">${historyEntry.name}</a>`;
            if (i == 0) nameEntry.innerHTML += ' (current)';

            let dateEntry = document.createElement('td');
            dateEntry.className = 'history-date';
            dateEntry.innerHTML = 'Original';
            if (historyEntry.changedToAt) dateEntry.innerHTML = `${formatDate(new Date(historyEntry.changedToAt))}`;

            historyRow.appendChild(nameIndex);
            historyRow.appendChild(nameEntry);
            historyRow.appendChild(dateEntry);

            rows.push(historyRow);
        }

        return rows;
    }

    // TODO: Possibly use for quick profile sharing?
    window.copyToClipboard = text => {
        navigator.clipboard.writeText(text)
            .then(() => { }, (err) => { console.error(err); });
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

    // Returns a Key-Value Pair/Map of all queries in the URL
    const getQuery = () => {
        if (!document.location.search.startsWith('?')) return [];

        let map = [];
        for (let query of window.location.search.substring(1).split('&')) {
            let data = query.split('=', 2);

            map.push(data[0]);
            map[data[0]] = data.length > 1 ? data[1] : '';
        }

        return map;
    };
})();