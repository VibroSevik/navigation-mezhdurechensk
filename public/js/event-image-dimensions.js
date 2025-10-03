document.addEventListener('DOMContentLoaded', function() {
    const vichImageContainers = document.getElementsByClassName('ea-vich-image');

    for (const container of vichImageContainers) {
        const vichImageInput = container.children[0].children[0].querySelector('input[type="file"]');
        vichImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];

            if (file && file.type.startsWith('image/')) {
                const image = new Image();
                const url = URL.createObjectURL(file);

                image.onload = function() {
                    container.children[1].textContent += `\nРазрешение: ${image.width}×${image.height} px`;
                    URL.revokeObjectURL(url);
                };

                image.src = url;
            }
        });
    }
});