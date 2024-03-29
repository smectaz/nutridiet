<?php

namespace App\Controller;

use App\Entity\Mark;
use App\Entity\Recipe;
use App\Form\MarkType;
use App\Form\RecipeType;
use App\Repository\MarkRepository;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RecipeController extends AbstractController
{
    /**
     * controller pour voir toute les recette avec pagination
     *
     * @param RecipeRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/recette', name: 'recipe.index', methods: ['GET'])]
    public function index(RecipeRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
        $cache = new FilesystemAdapter();
        $fullData = $cache->get('fullRecipes', function (ItemInterface $item) use ($repository) {
            $item->expiresAfter(15);
            return $repository->findBy(['user' => $this->getUser()]);
        });

        $recipes = $paginator->paginate(
            $fullData,
            $request->query->getInt('page', 1),
            9
        );

        return $this->render('pages/recipe/liste.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    /**
     * controller pour voir toute les recettes publiques
     *
     * @param RecipeRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return response
     */
    #[Route('/recette/communaute', name: 'recipe.community', methods: ['GET'])]
    public function indexPublic(RecipeRepository $repository, PaginatorInterface $paginator, Request $request): response
    {
        $cache = new FilesystemAdapter();
        $data = $cache->get('recipes', function (ItemInterface $item) use ($repository) {
            $item->expiresAfter(15);
            return $repository->findPublicRecipe(null);
        });

        $recipes = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('pages/recipe/community.html.twig', [
            'recipes' => $recipes
        ]);
    }

        /**
     * controller pour voir toute les recettes favorites
     *
     * @param RecipeRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return response
     */
    #[Route('/recette/favoris', name: 'recipe.favoris', methods: ['GET'])]
    public function indexFavorite(RecipeRepository $repository, PaginatorInterface $paginator, Request $request): response
    {
        $cache = new FilesystemAdapter();
        $favData = $cache->get('favRecipes', function (ItemInterface $item) use ($repository) {
            $item->expiresAfter(15);
            return $repository->findFavoriteRecipe(null);
        });

        $recipes = $paginator->paginate(
            $favData,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('pages/recipe/favoris.html.twig', [
            'recipes' => $recipes
        ]);
    }


    /**
     * controller qui permet de voir une recette publique
     *
     * @param Recipe $recipe
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and (recipe.getIsPublic() === true  || user === recipe.getUser())")]
    #[Route("/recette/vue/{id}", name: "recipe.show", methods: ['GET', 'POST'])]
    public function Show(Recipe $recipe, Request $request, MarkRepository $markRepository, EntityManagerInterface $manager): Response
    {
        $mark = new Mark();
        $form = $this->createForm(MarkType::class, $mark);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mark->setUser($this->getUser())
                ->setRecipe($recipe);

            $existingMark = $markRepository->findOneBy([
                'user' => $this->getUser(),
                'recipe' => $recipe
            ]);

            if (!$existingMark) {
                $manager->persist($mark);
            } else {
                $existingMark->setMark(
                    $form->getData()->getMark()
                );
            }

            $manager->flush();

            $this->addFlash(
                'success',
                'Votre note a bien été pris en compte.'
            );
            return $this->redirectToRoute('recipe.show', ['id' => $recipe->getId()]);
        }

        return $this->render('pages/recipe/show.html.twig', [
            'recipe' => $recipe,
            'form' => $form->createView()
        ]);
    }



    /**
     * controller pour creer une recette
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/recette/creation', name: "recipe.new", methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe, ['route' => 'recipe.new']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();
            $recipe->setUser($this->getUser());

            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre recette a été crée avec succès !'
            );

            return $this->redirectToRoute('recipe.index');
        }

        return $this->render('pages/recipe/ajout.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * controller pour modifier une recette
     *
     * @param Recipe $recipe
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user === recipe.getUser()")]
    #[route('/recette/edition/{id}', name: 'recipe.edit', methods: ['GET', 'POST',])]
    public function edit(Recipe $recipe, Request $request, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(RecipeType::class, $recipe, ['route' => 'recipe.edit']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();
            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre recette a été modifié avec succès !'
            );

            return $this->redirectToRoute('recipe.index');
        }

        return $this->render('pages/recipe/modif.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * controller pour supprimer une recette
     *
     * @param Recipe $recipe
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Security("is_granted('ROLE_USER') and user === recipe.getUser()")]
    #[Route('/recette/suppression/{id}', name: 'recipe.delete', methods: ['GET', 'POST'])]
    public function delete(EntityManagerInterface $manager, Recipe $recipe): Response
    {
        $manager->remove($recipe);
        $manager->flush();

        $this->addFlash(
            'success',
            'la recette a été supprimé avec succès !'
        );

        return $this->redirectToRoute('recipe.index');
    }
}
