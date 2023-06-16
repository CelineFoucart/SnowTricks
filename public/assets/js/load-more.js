function loadMore(type, offset, loadBtn) {
    let route;
    if (type === 'trick') {
        route = '/api/trick/' + offset;
    } else {
        route = '';
    }

    const request = new Request(route);
    fetch(request)
        .then(function (response) {
            if (!response.ok) {
                throw new Error(`erreur HTTP! statut: ${response.status}`);
            }
            return response.json();
        })
        .then(function (data) {
            const parser = new DOMParser();
            const html = parser.parseFromString(data.tricks, "text/html");
            const container = document.querySelector('#load-container');
            container.innerHTML = container.innerHTML + html.body.innerHTML;

            if (data.offset > data.total) {
                loadBtn.remove();
            } else {
                loadBtn.dataset.offset = data.offset;
            }
        })
    ;
}

const btn = document.querySelector('#load-more');
if (btn) {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        const type = btn.dataset.type;
        const offset = btn.dataset.offset;
        loadMore(type, offset, btn);
    });
}