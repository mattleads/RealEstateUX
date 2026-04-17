<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Cropperjs\Factory\CropperInterface;
use Symfony\UX\Cropperjs\Form\CropperType;
use App\Form\PropertyType;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Property;
use App\Service\FileUploader;
use Symfony\UX\Cropperjs\Model\Crop;

final class PropertyController extends AbstractController
{
    public function __construct(
        private readonly CropperInterface $cropper,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/property', name: 'app_property_index')]
    public function index(): Response
    {
        return $this->render('property/index.html.twig', [
            'properties' => $this->entityManager->getRepository(Property::class)->findAll(),
        ]);
    }

    #[Route('/property/new', name: 'app_property_new')]
    public function new(Request $request, FileUploader $uploader): Response
    {
        $property = new Property();
        $form = $this->createForm(PropertyType::class, $property);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photo')->getData();

            if ($photoFile) {
                $property->setImageUrl($uploader->upload($photoFile));
            }

            $this->entityManager->persist($property);
            $this->entityManager->flush();

            $this->addFlash('success', 'Property created successfully!');

            if ($photoFile) {
                return $this->redirectToRoute('app_property_crop', ['id' => $property->getId()]);
            }
            return $this->redirectToRoute('app_property_index');
        }

        return $this->render('property/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/property/{id}/edit', name: 'app_property_edit')]
    public function edit(Request $request, Property $property, FileUploader $uploader): Response
    {
        $form = $this->createForm(PropertyType::class, $property);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photo')->getData();

            if ($photoFile) {
                $property->setImageUrl($uploader->upload($photoFile));
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Property updated successfully!');

            if ($photoFile) {
                return $this->redirectToRoute('app_property_crop', ['id' => $property->getId()]);
            }
            return $this->redirectToRoute('app_property_index');
        }

        return $this->render('property/edit.html.twig', [
            'property' => $property,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/property/{id}/crop', name: 'app_property_crop')]
    public function crop(Request $request, Property $property): Response
    {
        if (!$property->getImageUrl()) {
            return $this->redirectToRoute('app_property_index');
        }

        $projectDir = $this->getParameter('kernel.project_dir');
        $imagePath = $projectDir . '/public' . $property->getImageUrl();

        $crop = $this->cropper->createCrop($imagePath);
        $crop->setCroppedMaxSize(1920, 1080);

        $form = $this->createFormBuilder(['photo' => $crop])
            ->add('photo', CropperType::class, [
                'public_url' => $property->getImageUrl(),
                'cropper_options' => [
                    'aspectRatio' => 16 / 9,
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Crop $cropData */
            $cropData = $form->get('photo')->getData();

            // Apply the crop and overwrite the original file
            $croppedImageContent = $cropData->getCroppedImage();
            file_put_contents($imagePath, $croppedImageContent);

            $this->addFlash('success', 'Photo cropped beautifully!');
            return $this->redirectToRoute('app_property_index');
        }

        return $this->render('property/crop.html.twig', [
            'property' => $property,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/property/{id}/delete', name: 'app_property_delete', methods: ['POST'])]
    public function delete(Request $request, Property $property): Response
    {
        if ($this->isCsrfTokenValid('delete'.$property->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($property);
            $this->entityManager->flush();
            $this->addFlash('success', 'Property deleted successfully!');
        }

        return $this->redirectToRoute('app_property_index');
    }
}
