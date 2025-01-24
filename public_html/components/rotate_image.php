<?php
if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['cover_image']['tmp_name'];
    $fileName = time() . "_" . $_FILES['cover_image']['name'];
    $uploadPath = '../uploads/book_covers/' . $fileName;

    // Kontrollera att det är en bild
    $fileType = mime_content_type($fileTmpPath);
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

    if (in_array($fileType, $allowedTypes)) {
        // Om det är en giltig bild, spara den på servern
        move_uploaded_file($fileTmpPath, $uploadPath);
        echo 'Bild uppladdad och sparad!';
    } else {
        echo 'Endast bildfiler är tillåtna.';
    }
} else {
    echo 'Ingen bild uppladdad.';
}
?>

<!-- Trigger för modal -->
<button type="button" id="openModalBtn" class="btn btn-primary">Redigera Omslagsbild</button>

<!-- Modal för bildredigering -->
<div id="imageModal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
    <div class="modal-content bg-white p-4 rounded-lg w-11/12 sm:w-1/2">
        <button type="button" id="closeModalBtn" class="absolute top-2 right-2 text-black font-bold">X</button>

        <h2 class="text-xl text-center mb-4">Redigera Omslagsbild</h2>

        <!-- Bildredigerare (Preview och Cropper.js) -->
        <div>
            <input type="file" id="cover_image" accept="image/*" class="mb-4">
            <div class="image-container">
                <img id="imagePreview" src="" alt="Preview" class="w-full max-h-96 mx-auto">
            </div>
        </div>

        <div class="flex justify-between mt-4">
            <button type="button" id="rotateLeft" class="btn">Rotera Vänster</button>
            <button type="button" id="rotateRight" class="btn">Rotera Höger</button>
        </div>

        <button type="button" id="saveImageBtn" class="btn mt-4 w-full bg-teal-500 text-white">Spara Bild</button>
    </div>
</div>


<script>
    var cropper;
    var modal = document.getElementById('imageModal');
    var openModalBtn = document.getElementById('openModalBtn');
    var closeModalBtn = document.getElementById('closeModalBtn');
    var saveImageBtn = document.getElementById('saveImageBtn');
    var rotateLeftBtn = document.getElementById('rotateLeft');
    var rotateRightBtn = document.getElementById('rotateRight');
    var imageInput = document.getElementById('cover_image');
    var imagePreview = document.getElementById('imagePreview');

    // Öppna modal
    openModalBtn.addEventListener('click', function () {
        modal.classList.remove('hidden');
    });

    // Stäng modal
    closeModalBtn.addEventListener('click', function () {
        modal.classList.add('hidden');
        if (cropper) {
            cropper.destroy(); // Rensa croppern när modalen stängs
        }
    });

    // Ladda upp och visa bild i Cropper.js
    imageInput.addEventListener('change', function (event) {
        var file = event.target.files[0];
        var reader = new FileReader();

        reader.onload = function (e) {
            imagePreview.src = e.target.result;

            // Initiera cropper
            if (cropper) {
                cropper.destroy(); // Rensa tidigare cropper
            }

            cropper = new Cropper(imagePreview, {
                autoCropArea: 0.65,
                viewMode: 1,
                rotatable: true,
                responsive: true,
                aspectRatio: NaN,  // Låt cropper vara fri i aspekt
                zoomable: true,
                movable: true,
                scalable: true
            });
        };
        reader.readAsDataURL(file);
    });

    // Rotera bild vänster
    rotateLeftBtn.addEventListener('click', function () {
        if (cropper) {
            cropper.rotate(-90);
        }
    });

    // Rotera bild höger
    rotateRightBtn.addEventListener('click', function () {
        if (cropper) {
            cropper.rotate(90);
        }
    });

    // Spara den roterade bilden
    saveImageBtn.addEventListener('click', function () {
        if (cropper) {
            cropper.getCroppedCanvas().toBlob(function (blob) {
                var formData = new FormData();
                formData.append('cover_image', blob);

                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'upload_image.php', true);

                xhr.onload = function () {
                    if (xhr.status === 200) {
                        alert('Bild uppladdad!');
                        modal.classList.add('hidden'); // Stäng modalen efter uppladdning
                    } else {
                        alert('Uppladdning misslyckades');
                    }
                };

                xhr.send(formData);
            });
        }
    });


</script>