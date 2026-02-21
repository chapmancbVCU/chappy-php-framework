<?php
declare(strict_types=1);
namespace Core\Lib\Database\Factories;

use Console\Helpers\Tools;
use Core\Models\ProfileImages;
use Core\Lib\Database\Factory;
use Smknstd\FakerPicsumImages\FakerPicsumImagesProvider;

/**
 * Factory for generating new profile images.
 */
class ProfileImageFactory extends Factory {
    protected string $modelName = ProfileImages::class;
    private int $userId;

    /**
     * Creates new instance of this factory.  You must provide user id
     * to constructor.
     *
     * @param int $userId The user_id for the profile image.
     */
    public function __construct(int $userId)
    {
        $this->userId = $userId;
        parent::__construct();
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected function definition(): array
    {
        $this->faker->addProvider(new FakerPicsumImagesProvider($this->faker));
        $basePath = 'storage' . DS . 'app' . DS . 'private' . DS . 'profile_images' . DS;
        $uploadPath = $basePath . 'user_' . $this->userId . DS;
        Tools::pathExists($uploadPath);

        // Generate the image and get the actual filename from Faker
        $actualFilePath = $this->faker->image($uploadPath, 200, 200, false, null, false, 'jpg');
        
        // Extract only the filename
        $imageFileName = basename($actualFilePath);
        ProfileImages::findAllByUserId($this->userId);
        $sort = ProfileImages::count();
        return [
            'user_id' => $this->userId,
            'sort' => $sort,
            'name' => $imageFileName,
            'url' => $uploadPath . $imageFileName
        ];
    }
}