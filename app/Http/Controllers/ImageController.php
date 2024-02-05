<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class ImageController extends Controller
{
     public function uploadImage(Request $request)
    {
        try {
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filePath = public_path('uploads/' . $file->getClientOriginalName());

                // Сохраняем загруженное изображение
                $file->move(public_path('uploads'), $file->getClientOriginalName());

                // Проверяем, успешно ли произошло сохранение файла
                if (!file_exists($filePath)) {
                    throw new \Exception('Failed to save uploaded image.');
                }

                // Создаем объект изображения
                $image = Image::make($filePath);

                // Проверяем, успешно ли произошло создание объекта изображения
                if (!$image) {
                    throw new \Exception('Failed to create Image object.');
                }

                // Получаем основной цвет
                $mainColor = $this->getMainColor($image);

                // Добавляем водяной знак с цветом, зависящим от основного цвета
                $watermarkedImage = $this->addWatermark($image, $mainColor);

                // Генерируем уникальное имя файла
                $filename = 'watermarked_' . time() . '_' . uniqid() . '.png';

                // Сохраняем измененное изображение
                $watermarkedImage->save(public_path('uploads/' . $filename));

                return response()->json([
                    'mainColor' => $mainColor,
                    'watermarkedImage' => asset('uploads/' . $filename),
                ]);
            } else {
                return response()->json(['error' => 'Выберите изображение']);
            }
        } catch (\Exception $e) {
            Log::error('Error processing image: ' . $e->getMessage());

            return response()->json(['error' => 'Произошла ошибка при обработке изображения.']);
        }
    }

    private function getMainColor($image)
    {
        $width = $image->getWidth();
        $height = $image->getHeight();
    
        // Округляем значения до целых чисел
        $pixelX = round($width / 2);
        $pixelY = round($height / 2);
    
        // Получаем цвет пикселя из середины изображения
        $pixelColor = $image->pickColor($pixelX, $pixelY);
    
        // Извлекаем значения R, G, B
        $red = $pixelColor[0];
        $green = $pixelColor[1];
        $blue = $pixelColor[2];
    
        // Определяем, к какому основному цвету (R, G, B) ближе
        if ($red > $green && $red > $blue) {
            return 'red';
        } elseif ($green > $red && $green > $blue) {
            return 'green';
        } else {
            return 'blue';
        }
    }

    private function addWatermark($image, $mainColor)
{
    // Определяем путь к изображению водяного знака в зависимости от основного цвета
    switch ($mainColor) {
        case 'red':
            $watermarkPath = public_path('uploads/watermarks/bkc_black.png');
            break;
        case 'green':
            $watermarkPath = public_path('uploads/watermarks/bkc_red.png');
            break;
        case 'blue':
            $watermarkPath = public_path('uploads/watermarks/bkc_yellow.png');
            break;
        default:
            $watermarkPath = public_path('uploads/watermarks/bkc_black.png');
    }

    // Загружаем изображение водяного знака
    $watermark = Image::make($watermarkPath);

   // Рассчитываем размер водяного знака (ширина 80% от ширины изображения)
    $watermarkWidth = $image->getWidth() * 0.8;
    $watermarkSize = min($watermarkWidth, $image->getHeight()) * 0.1;
    
    // Увеличиваем ширину водяного знака
    $watermark = $watermark->resize($watermarkWidth, null, function ($constraint) {
        $constraint->aspectRatio();
    });
    
    // Вставляем изображение водяного знака в центр оригинального изображения
    $watermarkedImage = $image->insert($watermark, 'center', 0, 0);

    return $watermarkedImage;
}
}

