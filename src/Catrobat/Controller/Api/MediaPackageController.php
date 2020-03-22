<?php

namespace App\Catrobat\Controller\Api;

use App\Catrobat\StatusCode;
use App\Entity\MediaPackage;
use App\Entity\MediaPackageCategory;
use App\Entity\MediaPackageFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MediaPackageController extends AbstractController
{
  /**
   * @deprecated
   *
   * @Route("/api/media/json", name="api_media_lib", defaults={"_format": "json"}, methods={"GET"})
   */
  public function getMediaLib(): JsonResponse
  {
    $em = $this->getDoctrine()->getManager();
    $media_package_files = $em->getRepository(MediaPackageFile::class)
      ->findAll()
    ;
    $json_response_array = [];
    if (null === $media_package_files || empty($media_package_files))
    {
      return JsonResponse::create(
        $json_response_array
      );
    }
    foreach ($media_package_files as $media_package_file)
    {
      /* @var MediaPackageFile $media_package_file */
      $json_response_array[] = $this->createArrayOfMediaData($media_package_file);
    }

    return JsonResponse::create($json_response_array);
  }

  /**
   * @deprecated
   *
   * @Route("/api/media/category/json", name="api_media_lib_all_category",
   * defaults={"_format": "json"}, methods={"GET"})
   */
  public function getCategories(Request $request): JsonResponse
  {
    /**
     * @var array[MediaPackageCategory]
     */
    $em = $this->getDoctrine()->getManager();
    $categories = $em->getRepository(MediaPackageCategory::class)->findAll();

    if (null === $categories || empty($categories))
    {
      return JsonResponse::create(
        [
          'statusCode' => StatusCode::MEDIA_LIB_CATEGORY_NOT_FOUND,
          'message' => 'No category found.',
        ]
      );
    }
    $json_response_array = [];
    foreach ($categories as $category)
    {
      $json_response_array[] = $this->createArrayOfCategory($category);
    }

    $flavor = $request->get('flavor');

    if ('pocketcode' !== $flavor)
    {
      $snowflake = [
        'id' => PHP_INT_MAX,
        'name' => $flavor,
        'displayID' => str_replace(' ', '', $flavor),
      ];
      $json_response_array[] = $snowflake;
    }

    return JsonResponse::create([
      'statusCode' => StatusCode::OK,
      'data' => $json_response_array,
    ]
    );
  }

  /**
   * @deprecated
   *
   * @Route("/api/media/category/{category}/json", name="api_media_lib_category",
   * requirements={"category": "\w+"}, defaults={"_format": "json"}, methods={"GET"})
   *
   * @param $category
   */
  public function getMediaFilesForCategory($category): JsonResponse
  {
    $em = $this->getDoctrine()->getManager();
    $json_response_array = [];

    $media_package_categories = $em->getRepository(MediaPackageCategory::class)
      ->findBy(
        [
          'name' => $category,
        ])
    ;
    if (null === $media_package_categories || count($media_package_categories) <= 0)
    {
      return JsonResponse::create(
        [
          'statusCode' => StatusCode::MEDIA_LIB_CATEGORY_NOT_FOUND,
          'message' => 'category '.$category.' not found',
        ]
      );
    }

    foreach ($media_package_categories as $media_package_category)
    {
      /**
       * @var array|MediaPackageFile
       * @var array|MediaPackageCategory $media_package_category
       */
      $media_package_files = $media_package_category->getFiles();
      if (null !== $media_package_files && (is_countable($media_package_files) ? count($media_package_files) : 0) > 0)
      {
        foreach ($media_package_files as $media_package_file)
        {
          $json_response_array[] = $this->createArrayOfMediaData($media_package_file);
        }
      }
    }

    return JsonResponse::create([
      'statusCode' => StatusCode::OK,
      'data' => $json_response_array,
    ]
    );
  }

  /**
   * @deprecated
   *
   * @Route("/api/media/package/{package}/json", name="api_media_lib_package",
   * requirements={"package": "\w+"}, defaults={"_format": "json"}, methods={"GET"})
   *
   * @param $package
   */
  public function getMediaFilesForPackage($package): JsonResponse
  {
    $em = $this->getDoctrine()->getManager();
    $media_package = $em->getRepository(MediaPackage::class)
      ->findOneBy(['name' => $package])
    ;
    if (null === $media_package)
    {
      return JsonResponse::create(
        ['statusCode' => StatusCode::MEDIA_LIB_PACKAGE_NOT_FOUND,
          'message' => $package.' not found', ]
      );
    }
    $json_response_array = [];
    /** @var array|MediaPackageCategory $media_package_categories */
    $media_package_categories = $media_package->getCategories();
    if (null === $media_package_categories || empty($media_package_categories))
    {
      return JsonResponse::create(
        $json_response_array
      );
    }
    foreach ($media_package_categories as $media_package_category)
    {
      /** @var array|MediaPackageFile $media_package_files */
      $media_package_files = $media_package_category->getFiles();
      if (null !== $media_package_files && (is_countable($media_package_files) ? count($media_package_files) : 0) > 0)
      {
        foreach ($media_package_files as $media_package_file)
        {
          $json_response_array[] = $this->createArrayOfMediaData($media_package_file);
        }
      }
    }

    return JsonResponse::create(
      $json_response_array
    );
  }

  /**
   * @deprecated
   *
   * @Route("/api/media/packageByNameUrl/{package}/json", name="api_media_lib_package_bynameurl",
   * requirements={"package": "\w+"}, defaults={"_format": "json"}, methods={"GET"})
   *
   * @param $package
   */
  public function getMediaFilesForPackageByNameUrl($package): JsonResponse
  {
    $em = $this->getDoctrine()->getManager();
    $media_package = $em->getRepository(MediaPackage::class)
      ->findOneBy(['nameUrl' => $package])
    ;
    if (null === $media_package)
    {
      return JsonResponse::create(
        ['statusCode' => StatusCode::MEDIA_LIB_PACKAGE_NOT_FOUND,
          'message' => $package.' not found', ]
      );
    }
    $json_response_array = [];
    /** @var array|MediaPackageCategory $media_package_categories */
    $media_package_categories = $media_package->getCategories();
    if (null === $media_package_categories || empty($media_package_categories))
    {
      return JsonResponse::create(
        $json_response_array
      );
    }
    foreach ($media_package_categories as $media_package_category)
    {
      /** @var array|MediaPackageFile $media_package_files */
      $media_package_files = $media_package_category->getFiles();
      if (null !== $media_package_files && (is_countable($media_package_files) ? count($media_package_files) : 0) > 0)
      {
        foreach ($media_package_files as $media_package_file)
        {
          $json_response_array[] = $this->createArrayOfMediaData($media_package_file);
        }
      }
    }

    return JsonResponse::create(
      $json_response_array
    );
  }

  /**
   * @deprecated
   *
   * @Route("/api/media/package/{package}/{category}/json", name="api_media_lib_package_category",
   *     requirements={"package": "\w+", "category": "\w+"},
   * defaults={"_format": "json"}, methods={"GET"})
   *
   * @param $package
   * @param $category
   */
  public function getMediaFilesForPackageAndCategory($package, $category): JsonResponse
  {
    $em = $this->getDoctrine()->getManager();
    $media_package = $em->getRepository(MediaPackage::class)
      ->findOneBy(['name' => $package])
    ;
    if (null === $media_package)
    {
      return JsonResponse::create(
        [
          'statusCode' => StatusCode::MEDIA_LIB_PACKAGE_NOT_FOUND,
          'message' => $package.' not found',
        ]
      );
    }
    $json_response_array = [];
    $category_not_found = true;
    /** @var array|MediaPackageCategory $media_package_categories */
    $media_package_categories = $media_package->getCategories();
    if (null === $media_package_categories || empty($media_package_categories))
    {
      return JsonResponse::create(
        [
          'statusCode' => StatusCode::MEDIA_LIB_CATEGORY_NOT_FOUND,
          'message' => 'category '.$category.' not found in package '.$package.
            " because the package doesn't contain any categories",
        ]
      );
    }
    foreach ($media_package_categories as $media_package_category)
    {
      // case insensitive:
      if (0 === strcasecmp($media_package_category->getName(), $category))
      {
        // case sensitive:
        // if ($media_package_category->getName() === $category)
        $category_not_found = false;
        /** @var array|MediaPackageFile $media_package_files */
        $media_package_files = $media_package_category->getFiles();
        if (null !== $media_package_files && (is_countable($media_package_files) ? count($media_package_files) : 0) > 0)
        {
          foreach ($media_package_files as $media_package_file)
          {
            $json_response_array[] = $this->createArrayOfMediaData($media_package_file);
          }
        }
      }
    }
    if ($category_not_found)
    {
      return JsonResponse::create(
        [
          'statusCode' => StatusCode::MEDIA_LIB_CATEGORY_NOT_FOUND,
          'message' => 'category '.$category.' not found in package '.$package,
        ]
      );
    }

    return JsonResponse::create(
      $json_response_array
    );
  }

  /**
   * @deprecated
   *
   * @Route("/api/media/file/{id}/json", name="api_media_lib_file", requirements={"id": "\d+"},
   * defaults={"id": 0, "_format": "json"}, methods={"GET"})
   *
   * @param $id
   */
  public function getSingleMediaFile($id): JsonResponse
  {
    if (0 === $id)
    {
      return JsonResponse::create(
        [
          'statusCode' => Response::HTTP_NOT_FOUND,
        ]
      );
    }
    $em = $this->getDoctrine()->getManager();
    $media_file = $em->getRepository(MediaPackageFile::class)
      ->find($id)
    ;
    if (null === $media_file)
    {
      return JsonResponse::create(
        [
          'statusCode' => Response::HTTP_NOT_FOUND,
        ]
      );
    }

    return JsonResponse::create(
      $this->createArrayOfMediaData($media_file)
    );
  }

  /**
   * @param $category MediaPackageCategory
   */
  private function createArrayOfCategory($category): array
  {
    $id = $category->getId();
    $name = $category->getName();

    return
      [
        'id' => $id,
        'name' => $name,
        'displayID' => str_replace(' ', '', $name),
      ];
  }

  /**
   * @param $media_package_file MediaPackageFile
   */
  private function createArrayOfMediaData($media_package_file): array
  {
    /**
     * @var MediaPackageFile
     * @var MediaPackage     $package
     */
    $id = $media_package_file->getId();
    $name = $media_package_file->getName();
    $flavor = $media_package_file->getFlavor();
    $package = $media_package_file->getCategory()->getPackage()->first()->getName();
    $category = $media_package_file->getCategory()->getName();
    $author = $media_package_file->getAuthor();
    $extension = $media_package_file->getExtension();
    $url = $media_package_file->getUrl();
    $download_url = $this->generateUrl('download_media',
      [
        'id' => $id,
      ]);

    return
      [
        'id' => $id,
        'name' => $name,
        'flavor' => $flavor,
        'package' => $package,
        'category' => $category,
        'author' => $author,
        'extension' => $extension,
        'url' => $url,
        'download_url' => $download_url,
      ];
  }
}
