<?php

namespace App\Controller;

use App\Entity\Auteur;
use App\Repository\AuteurRepository;
use App\Repository\NationaliteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiAuteurController extends AbstractController
{
    /**
     * @Route("/api/auteurs", name="api_auteurs", methods={"GET"})
     */
    public function list(AuteurRepository $repo, SerializerInterface $serializer)
    {
        $auteurs = $repo->findALL();
        $resultat = $serializer->serialize(
            $auteurs,
            "json",
            [
                'groups' => ['listeAuteurFull']
            ]
        );
        return new JsonResponse($resultat, 200, [], true);
    }

    /**
     * @Route("/api/auteurs/{id}", name="api_auteurs_show", methods={"GET"})
     */
    public function show(Auteur $auteur, SerializerInterface $serializer)
    {
        $resultat = $serializer->serialize(
            $auteur,
            "json",
            [
                'groups' => ['listAuteurSimple']
            ]
        );
        return new JsonResponse($resultat, Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/api/auteurs", name="api_auteurs_create", methods={"POST"})
     */
    public function create(NationaliteRepository $repNationalite, Request $request, SerializerInterface $serializer, EntityManagerInterface $manager, ValidatorInterface $validator)
    {
        $data = $request->getContent();
        $dataTab = $serializer->decode($data, 'json');
        $auteur = new Auteur();
        $nationalite = $repNationalite->find($dataTab['relation']['id']);
        $serializer->deserialize($data, Auteur::class, 'json', ['object_to_populate' => $auteur]);
        $auteur->setRelation($nationalite);
        $errors = $validator->validate($auteur);
        if (count($errors)) {
            $errorsJson = $serializer->serialize($errors, 'json');

            return new JsonResponse($errorsJson, Response::HTTP_BAD_REQUEST, [], true);
        }
        $manager->persist($auteur);
        $manager->flush();
        return new JsonResponse(
            "Le nouveau auteur a été crée",
            Response::HTTP_CREATED,
            [
                ["location" => $this->generateUrl(
                    'api_auteurs_show',
                    ["id" => $auteur->getId()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )],
            ],
            true
        );
    }


    /**
     * @Route("/api/auteurs/{id}", name="api_auteurs_update", methods={"PUT"})
     */
    public function edit(NationaliteRepository $repNationalite, Auteur $auteur, SerializerInterface $serializer, Request $request, EntityManagerInterface $manager, ValidatorInterface $validator)
    {
        $data = $request->getContent();
        $dataTab = $serializer->decode($data, 'json');
        $nationalite = $repNationalite->find($dataTab['relation']['id']);
        $serializer->deserialize($data, Auteur::class, 'json', ['object_to_populate' => $auteur]);
        $auteur->setRelation($nationalite);
        $errors = $validator->validate($auteur);
        if (count($errors)) {
            $errorsJson = $serializer->serialize($errors, 'json');
            return new JsonResponse($errorsJson, Response::HTTP_BAD_REQUEST, [], true);
        }
        $manager->persist($auteur);
        $manager->flush();
        return new JsonResponse("Le auteur a bien été modifié ", Response::HTTP_OK, [], true);
    }


    /**
     * @Route("/api/auteurs/{id}", name="api_auteurs_delete", methods={"DELETE"})
     */
    public function deleteAuteur(Auteur $auteur, EntityManagerInterface $manager)
    {
        $manager->remove($auteur);
        $manager->flush();
        return new JsonResponse("Le auteur a bien été supprimé ", Response::HTTP_OK, [], true);
    }
}
