/*
 * Proxatore, a proxy for viewing and embedding content from various platforms.
 * Copyright (C) 2025 OctoSpacc
 *
*/

(function(){

const groupLink = (group) => `?proxatore-group=${encodeURIComponent(JSON.stringify(group))}`;
const groupRedirect = (group) => location.href = groupLink(group);
const groupPersist = (group) => localStorage.setItem('proxatore-group', group.length ? JSON.stringify(group) : null);
const groupUpdate = (group) => {
    groupPersist(group);
    groupRedirect(group);
};
const moveItem = (data, from, to) => data.splice(to, 0, data.splice(from, 1)[0]);

const openingGroup = JSON.parse((new URLSearchParams(location.search)).get('proxatore-group'));
const editingGroup = JSON.parse(localStorage.getItem('proxatore-group'));
let group = openingGroup || editingGroup;
if (group) {
    document.querySelector('form').innerHTML += '<details id="ProxatoreGroup" style="margin-bottom: 20px;"><summary>Results Group</summary><ul></ul></details>';
    if (editingGroup) {
        ProxatoreGroup.open = true;
        ProxatoreGroup.querySelector('summary').innerHTML = `<a href="${groupLink(group)}">Results Group</a>`;
    }
    ProxatoreGroup.querySelector('summary').innerHTML += ` <button>${editingGroup ? 'Cancel' : 'Edit'}</button>`;
    ProxatoreGroup.querySelector('summary button').addEventListener('click', (ev) => {
        ev.preventDefault();
        groupUpdate(editingGroup ? [] : group);
    });
    ProxatoreGroup.querySelector('ul').innerHTML = Object.keys(group).map(id => `<li data-id="${id}">
        <button class="up">⬆</button> <button class="down">⬇</button> <button class="remove">Remove</button>
        <code><a href="<?= makeSelfUrl() ?>${group[id]}">${group[id]}</a></code>
    </li>`).join('');
    ProxatoreGroup.querySelectorAll('ul button.remove').forEach(button => button.addEventListener('click', (ev) => {
        ev.preventDefault();
        group.splice(button.parentElement.dataset.id, 1);
        groupUpdate(group);
    }));
    ProxatoreGroup.querySelectorAll('ul button.up').forEach(button => button.addEventListener('click', (ev) => {
        ev.preventDefault();
        const id = button.parentElement.dataset.id;
        moveItem(group, id, id-1);
        groupUpdate(group);
    }));
    ProxatoreGroup.querySelectorAll('ul button.down').forEach(button => button.addEventListener('click', (ev) => {
        ev.preventDefault();
        const id = button.parentElement.dataset.id;
        moveItem(group, id, id+1);
        groupUpdate(group);
    }));
    ProxatoreGroup.querySelector('ul li:first-of-type button.up').disabled = ProxatoreGroup.querySelector('ul li:last-of-type button.down').disabled = true;
} else {
    group = [];
}

document.querySelectorAll('.actions').forEach(item => {
    item.innerHTML += `<button class="button block">Add to Results Group</button>`;
    item.querySelector('button').addEventListener('click', () => {
        group.push(item.querySelector('a.internal').getAttribute('href'));
        groupUpdate(group);
    });
});

})();