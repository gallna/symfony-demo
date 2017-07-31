<?php

namespace AddressBookBundle\Controller;

use AppBundle\Utils\Slugger;
use AddressBookBundle\Repository\AddressBookRepository;
use AddressBookBundle\Entity\Contact;
use AddressBookBundle\Form\ContactType;
use AddressBookBundle\Form\EmailType;
use AddressBookBundle\Form\PhoneType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller used to manage blog contents in the backend.
 *
 * Please note that the application backend is developed manually for learning
 * purposes. However, in your real Symfony application you should use any of the
 * existing bundles that let you generate ready-to-use backends without effort.
 *
 * @Route("/contact")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class ContactController extends Controller
{

    /**
     * Creates a new Contact entity.
     *
     * @Route("/new", name="contact_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $contact = new Contact();
        $contact->setUsername($this->getUser()->getUsername());
        $contact->setName("abc def");
        $contact->setEmail("abc@def.gh");
        $contact->setPhone(123456);
        // See https://symfony.com/doc/current/book/forms.html#submitting-forms-with-multiple-buttons
        $form = $this->createForm(ContactType::class, $contact)
            ->add('saveAndCreateNew', SubmitType::class);

        $form->handleRequest($request);


        // the isSubmitted() method is completely optional because the other
        // isValid() method already checks whether the form is submitted.
        // However, we explicitly add it to improve code readability.
        // See https://symfony.com/doc/current/best_practices/forms.html#handling-form-submits
        if ($form->isSubmitted() && $form->isValid()) {

            $repository = $this->container->get(AddressBookRepository::class);
            $contact->setUsername($this->getUser()->getUsername());
            $repository->add($contact);

            // Flash messages are used to notify the user about the result of the
            // actions. They are deleted automatically from the session as soon
            // as they are accessed.
            // See https://symfony.com/doc/current/book/controller.html#flash-messages
            $this->addFlash('success', 'contact.created_successfully');

            if ($form->get('saveAndCreateNew')->isClicked()) {
                return $this->redirectToRoute('contact_new');
            }

            return $this->redirectToRoute('address_book_list');
        }

        return $this->render('address-book/new.html.twig', [
            'post' => $contact,
            'form' => $form->createView(),
        ]);
    }


    /**
     * Displays a form to edit an existing Contact entity.
     *
     * @Route("/edit/{slug}", name="contact_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction($slug, Request $request, AddressBookRepository $repository)
    {
        // $this->denyAccessUnlessGranted('edit', $contact, 'Contacts can only be edited by their authors.');
        $contact = $repository->find($this->getUser()->getUsername(), $slug);

        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contact->setUsername($this->getUser()->getUsername());
            $repository->update($slug, $contact);

            $this->addFlash('success', 'Contact updated successfully');

            return $this->redirectToRoute('address_book_item', ['slug' => $contact->getSlug()]);
        }

        return $this->render('address-book/edit.html.twig', [
            'post' => $contact,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes a Contact entity.
     *
     * @Route("/delete/{slug}", name="contact_delete")
     * @Method("POST")
     */
    public function deleteAction($slug, Request $request, AddressBookRepository $repository)
    {
        if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
            return $this->redirectToRoute('address_book_list');
        }

        // Delete the tags associated with this blog post. This is done automatically
        // by Doctrine, except for SQLite (the database used in this application)
        // because foreign key support is not enabled by default in SQLite
        $contact = $repository->find($this->getUser()->getUsername(), $slug);
        $repository->remove($contact);

        $this->addFlash('success', 'Contact deleted successfully!');

        return $this->redirectToRoute('address_book_list');
    }
}
