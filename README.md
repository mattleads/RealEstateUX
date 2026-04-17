# Real Estate UX - Symfony UX 3.0 Demo

A modern real estate listing prototype built to showcase the powerful new features and streamlined workflows introduced in **Symfony UX 3.0.0**. This project utilizes PHP 8.4+, Symfony 7.4+, and AssetMapper (no Node.js required).

## Features Highlight
* **Twig Components 3.0:** Utilizing `html_cva` from `twig/html-extra` for Tailwind CSS class variant management.
* **Smart Forms:** `ux-autocomplete` 3.0 using the streamlined `BaseEntityAutocompleteType`.
* **Two-Step Image Upload & Cropping:** Combines native HTML5 Drag & Drop with `ux-cropperjs` 3.0 (with automatic EXIF rotation handling).
* **AssetMapper & Stimulus:** Custom native JavaScript integration replacing deprecated wrappers like `ux-typed`.
* **Clean Architecture:** Form logic is encapsulated in `PropertyType`, and file handling is delegated to a dedicated `FileUploader` service using PHP 8 autowiring.

## Setup Instructions

Ensure you have PHP 8.4+ and Composer 2.x installed.

**Clone the repository:**
   ```bash
   git clone https://github.com/mattleads/RealEstateUX.git
   cd RealEstateUX
   ```

**Setup and configure:**

```bash
# 1. Install dependencies
composer install

# 2. Setup the SQLite database & schema
php bin/console doctrine:schema:create

# 3. Seed the database with sample Amenities (Pool, Garage, etc.)
php bin/console app:seed-data

# 4. Download 3rd-party assets via AssetMapper
php bin/console importmap:install

# 5. Start the local web server
php -S localhost:8000 -t public   
# or symfony server:start
```

Navigate to `http://localhost:8000/property` to see the application in action.

---

## Examples of Usage

### 1. Twig Components & CVA (Property Card)
The `PropertyCard` component uses the new `html_cva` function to natively manage Tailwind utility classes based on the component's state (`active`, `sold`, `pending`).

**Definition (`src/Twig/Components/PropertyCard.php`):**
```php
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('PropertyCard')]
class PropertyCard
{
    public function __construct(
        public string $title = '',
        public int $price = 0,
        public string $status = 'active',
        public ?string $imageUrl = null,
        public ?int $id = null,
    ) {}
}
```

**Usage in Twig:**
```twig
{{ component('PropertyCard', {
    title: 'Modern Mansion',
    price: 2500000,
    status: 'active',
    imageUrl: 'https://example.com/mansion.jpg'
}) }}
```

### 2. Entity Autocomplete (Amenities)
We use `ux-autocomplete` to handle Many-to-Many entity selections without loading thousands of options into a standard HTML `<select>`.

**Usage in Form (`src/Form/PropertyType.php`):**
```php
$builder->add('amenities', AmenityAutocompleteField::class);
```

### 3. Drag & Drop + Image Cropping (Cover Photo)
The application uses a two-step workflow for the best user experience:
1. **Upload:** A custom Stimulus controller (`dropzone_controller.js`) handles HTML5 drag-and-drop events and provides an instant local preview using the `FileReader` API. The file is saved by the `FileUploader` service.
2. **Crop:** If a new photo is uploaded, the user is redirected to a dedicated cropping route where `ux-cropperjs` is used to enforce a strict 16:9 aspect ratio before finalizing the image.

**Cropper implementation in Controller:**
```php
use Symfony\UX\Cropperjs\Form\CropperType;

// Provide the existing image to the cropper
$crop = $this->cropper->createCrop($imagePath);
$crop->setCroppedMaxSize(1920, 1080);

$form = $this->createFormBuilder(['photo' => $crop])
    ->add('photo', CropperType::class, [
        'public_url' => $property->getImageUrl(),
        'cropper_options' => [
            'aspectRatio' => 16 / 9, // Enforce 16:9 ratio
        ],
    ])
    ->getForm();

// Upon submission:
$cropData = $form->get('photo')->getData();
$croppedImageContent = $cropData->getCroppedImage();
file_put_contents($imagePath, $croppedImageContent);
```

### 4. Custom Stimulus Controller via AssetMapper (Typing Effect)
Since `ux-typed` was removed in 3.0, we easily recreate it using AssetMapper and a standard Stimulus controller.

**AssetMapper Import:**
```bash
php bin/console importmap:require typed.js@2.1.0
```

**Usage in HTML/Twig:**
```html
<span 
    data-controller="typing" 
    data-typing-strings-value='["Mansion", "Cozy Condo", "Downtown Loft"]'
    data-typing-speed-value="75"
    class="text-blue-600"
></span>
```
