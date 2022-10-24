<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Recipe;
use App\Entity\Contact;
use App\Entity\Ingredient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    
    /**
     * controller pour gerer le dashboard de easy admin
     *
     * @return Response
     */
    #[Route('/admin', name: 'admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Nutridiet - Administration')
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Utilisateur', 'fas fa-user', User::class);
        yield MenuItem::linkToCrud('Demandes de contact', 'fas fa-envelope', Contact::class);
        yield MenuItem::linkToCrud('Ingrédient', 'fas fa-carrot', Ingredient::class);
        yield MenuItem::linkToCrud('Recette', 'fas fa-user-chef', Recipe::class);
        yield MenuItem::linkToRoute('accueil', 'fas fa-door-open', 'home.index');
    }
}
