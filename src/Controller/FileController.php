<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class FileController extends AbstractController
{
    #[Route('/harmonyhub/admin/upload', name: 'app_upload', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        $archivo = $request->files->get('file');
        if ($archivo) {
            $artista = $request->get('artista');
            $album = $request->get('album');

            $audioDirectory = $this->getParameter('audio_directory') . '/' . $artista . '/' . $album;

            if (!is_dir($audioDirectory)) {
                mkdir($audioDirectory, 0777, true);
            }

            $originalFilename = $archivo->getClientOriginalName();
            try {
                $archivo->move(
                    $audioDirectory,
                    $originalFilename
                );
                return new JsonResponse(['path' => $artista . '/' . $album . '/' . $originalFilename], Response::HTTP_OK);
            } catch (FileException $e) {
                return new JsonResponse(['error' => 'Error al subir el archivo'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return new JsonResponse(['error' => 'No se ha recibido ningún archivo'], Response::HTTP_BAD_REQUEST);
    }
}