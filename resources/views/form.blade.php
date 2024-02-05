<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавление водяного знака на изображение</title>
    <!-- Подключаем стили Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h1 class="text-center mb-4">Добавление водяного знака на изображение</h1>
            
            <form id="imageForm" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="image">Выберите изображение</label>
                    <input type="file" class="form-control-file" id="image" name="image">
                </div>
                <button type="button" class="btn btn-primary" onclick="submitForm()">Отправить</button>
            </form>

            <div style="display:none;" id="result" class="mt-3">
                <p><strong>Основной цвет:</strong> <span id="mainColor"></span></p>
                <img id="watermarkedImage" class="img-fluid" style="max-width: 100%;" alt="Watermarked Image">
                <button class="btn btn-success mt-3" id="downloadBtn">Скачать изображение</button>
            </div>
        </div>
    </div>
</div>

<!-- Подключаем скрипты Bootstrap и jQuery (для Ajax) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Дополняем ваши стили, если нужно -->
<style>
    #downloadBtn {
        margin-top: 10px;
    }
</style>

<script>
    function submitForm() {
        var formData = new FormData($('#imageForm')[0]);

        $.ajax({
            url: '{{ route("uploadImage") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $('#result').css('display', 'block');
                $('#mainColor').text(response.mainColor);
                $('#watermarkedImage').attr('src', response.watermarkedImage);
                $('#downloadBtn').attr('onclick', 'downloadImage("' + response.watermarkedImage + '")');
            },
            error: function (error) {
                console.log(error);
            }
        });
    }

    function downloadImage(imageUrl) {
        var link = document.createElement('a');
        link.href = imageUrl;
        link.download = 'watermarked_image.png';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>

</body>
</html>

