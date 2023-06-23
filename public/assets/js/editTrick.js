function defineTitle(inputTitle) {
    const titleBlock = document.querySelector('#trick-title');

    if (!titleBlock) {
        return;
    }

    if (inputTitle.value.length >= 1) {
        titleBlock.innerHTML = inputTitle.value;
    } else {
        titleBlock.innerHTML = "Aucun nom défini";
    }
}

function addMediaInput(containerSelector) {
    const blockImage = document.querySelector(containerSelector);

        if (!blockImage) {
            return;
        }

        let prototype = blockImage.dataset.prototype;
        let index = blockImage.dataset.index;

        if (!prototype) {
            return;
        }

        const parser = new DOMParser();
        prototype = prototype.replace(/__name__/g, index);
        const parsed = parser.parseFromString(prototype, 'text/html');
        const field = parsed.querySelector(containerSelector+'_'+index);
        field.classList = 'my-2 p-2 bg-light border rounded-3';
        index++;
        blockImage.setAttribute('data-index', index);
        blockImage.appendChild(field);
}

const btnImage = document.querySelector('#add-images');
if (btnImage) {
    btnImage.addEventListener('click', (e) => {
        e.preventDefault();
        addMediaInput('#trick_images');
    })
}

const btnVideo = document.querySelector('#add-videos');
if (btnVideo) {
    btnVideo.addEventListener('click', (e) => {
        e.preventDefault();
        addMediaInput('#trick_videos');
    })
}

const inputTitle = document.querySelector('#trick_name');
if (inputTitle) {
    inputTitle.addEventListener('keyup', () => {
        defineTitle(inputTitle);
    });

    defineTitle(inputTitle);
}

const element = document.querySelector('#trick_categories');
if (element) {
    new Choices(element, {
        removeItems: true,
        removeItemButton: true,
        allowHTML: false,
        noResultsText: 'Aucun résultat',
        noChoicesText: 'Aucun élément à choisir',
        itemSelectText: 'Cliquez pour choisir',
        shouldSort: false,
    });
}

const btnDeletes = document.querySelectorAll('.remove-btn');

btnDeletes.forEach(element => {
    element.addEventListener('click', (e) => {
        e.preventDefault();
        const field = document.querySelector(element.dataset.target);
        if (field) {
            field.remove();
        }
        
    });
});