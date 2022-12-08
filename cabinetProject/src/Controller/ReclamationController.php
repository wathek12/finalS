<?php

namespace App\Controller;


use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class ReclamationController extends AbstractController
{

    /**
     * @Route("/acceuil", name="acceuil")
     */
    public function index(): Response
    {
        return $this->render('reclamation/acceuil.html.twig', [
            'controller_name' => 'ReclamationController',
        ]);
    }



    /**
     * @Route("/afficherreclamation",name="afficherreclamation")
     */
    public function Affiche(Request $request,ReclamationRepository $repository,PaginatorInterface $paginator){
        $tablereclamation=$repository->listReclamationByDate();

        $tablereclamation = $paginator->paginate(
            $tablereclamation,
            $request->query->getInt('page', 1),
            2
        );

        return $this->render('reclamation/afficherreclamation.html.twig'
            ,['tablereclamation'=>$tablereclamation]);

    }
    /**
     * @Route("/ajouterreclamationback",name="ajouterreclamationback")
     */
    public function ajouterrec(EntityManagerInterface $em,Request $request ,ReclamationRepository $UserRepository,\Swift_Mailer $mailer ){
        $reclamation= new Reclamation();
        $form= $this->createForm(ReclamationType::class,$reclamation);
        $form->add('Ajouter',SubmitType::class);
        $form->handleRequest($request);
        $reclamation->setDate(new \DateTimeImmutable());
        if($form->isSubmitted() && $form->isValid()){
            $new=$form->getData();

            $imageFile = $form->get('img')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        'back\images',
                        $newFilename
                    );
                } catch (FileException $e) {
                }
                $reclamation->setImg($newFilename);
            }


            $em->persist($reclamation);
            $em->flush();
            $maile=[];
            $msg= $reclamation->getContenu();
            $email = (new Email())
                ->from('539da53abe94bb@smtp.mailtrap.io')
                ->to('wathekmth19@gmail.com')
                //->cc('cc@example.com')
                //->bcc('bcc@example.com')
                //->replyTo('fabien@example.com')
                //->priority(Email::PRIORITY_HIGH)
                ->subject('Nouvelle Reclamation!')
                ->text('Important!')
                ->html(`<p>$msg</p>`);

            $mailer->send($email);



            return $this->redirectToRoute("acceuil");
        }
        return $this->render("reclamation/ajouterreclamationback.html.twig",array("form"=>$form->createView()));

    }

    /**
     * @Route("/ajouterreclamationfront",name="ajouterreclamationfront")
     */
    public function ajouterreclamationfront(EntityManagerInterface $em,Request $request ,ReclamationRepository $UserRepository,MailerInterface $mailer ){
        $reclamation= new Reclamation();
        $form= $this->createForm(ReclamationType::class,$reclamation);
        $form->add('Ajouter',SubmitType::class);
        $form->handleRequest($request);
        $reclamation->setUser($this->getUser());
        $reclamation->setDate(new \DateTimeImmutable());
        if($form->isSubmitted() && $form->isValid()){
            $new=$form->getData();

            $imageFile = $form->get('img')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        'back\images',
                        $newFilename
                    );
                } catch (FileException $e) {
                }
                $reclamation->setImg($newFilename);
            }


            $em->persist($reclamation);
            $em->flush();
            $maile=[];
            $msg= $reclamation->getContenu();
            $email = (new Email())
                ->from('539da53abe94bb@smtp.mailtrap.io')
                ->to('wathekmth19@gmail.com')
                //->cc('cc@example.com')
                //->bcc('bcc@example.com')
                //->replyTo('fabien@example.com')
                //->priority(Email::PRIORITY_HIGH)
                ->subject('Nouvelle Reclamation!')
                ->text('Important!')
                ->html(`<p>$msg</p>`);

            $mailer->send($email);



            return $this->redirectToRoute("acceuil");
        }
        return $this->render("reclamation/ajouterreclamationfront.html.twig",array("form"=>$form->createView()));

    }
    /**
     * @Route("/supprimerreclamation/{id}",name="supprimerreclamation")
     */
    public function delete($id,EntityManagerInterface $em ,ReclamationRepository $repository){
        $reclamation=$repository->find($id);
        $em->remove($reclamation);
        $em->flush();

        return $this->redirectToRoute('afficherreclamation');
    }
    /**
     * @Route("/{id}/modifierreclamation", name="modifierreclamation", methods={"GET","POST"})
     */
    public function edit(Request $request, Reclamation $reclamation): Response
    {
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->add('Confirmer',SubmitType::class);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('img')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        'back\images',
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $reclamation->setImg($newFilename);
            }
            $this->getDoctrine()->getManager()->flush();


            return $this->redirectToRoute('afficherreclamation');
        }

        return $this->render('reclamation/modifierreclamation.html.twig', [
            'reclamationmodif' => $reclamation,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/pdf/{id}", name="pdf" ,  methods={"GET"})
     */
    public function pdf($id,ReclamationRepository $repository){

        $reclamation=$repository->find($id);

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('reclamation/pdf.html.twig', [
            'pdf' => $reclamation
        ]);
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();
        //$dompdf->stream();
        // Output the generated PDF to Browser (force download)
        $dompdf->stream($reclamation->getUser(), [
            "Attachment" => false
        ]);




    }



}
