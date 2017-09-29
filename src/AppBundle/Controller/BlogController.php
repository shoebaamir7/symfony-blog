<?php

namespace AppBundle\Controller;
use AppBundle\Entity\Blog;
use AppBundle\Entity\User;
use AppBundle\Entity\Comment;
use AppBundle\Form\BlogType;
use AppBundle\Form\CommentType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class BlogController extends Controller
{
    /**
     * @Route("/blogs", name="blog_list")
     */
    public function indexAction(Request $request)
    {
        $blogs = $this->getDoctrine()->getRepository(Blog::class)->findAll();
        return $this->render('Blog/list.html.twig', array('blogs'=> $blogs));
        
    }
    /**
     * @Route("/blog/create", name="blog_create")
     */
    public function createAction(Request $request)
    {
        $user = new User();
        $blog = new Blog();
        $blog->setUser($this->getUser());
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($blog);
            $em->flush();
            return $this->redirectToRoute('blog_list');
        }
        return $this->render('Blog/create.html.twig', array('form' => $form->createView()));
    }
    /**
     * @Route("/blog/show/{id}", name="blog_show")
     */
    public function showAction(Request $request,$id)
    {
        $blog = $this->getDoctrine()->getRepository(Blog::class)->findOneById($id);
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = new User();
            $comment->setUser($this->getUser());
            $comment->setBlog($blog);
            $comment->setstatus(0);
            if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
                $comment->setstatus(1);
            }
            $em = $this->getDoctrine()->getManager();
            $em->merge($comment);
            $em->flush();
            return $this->redirect($this->generateUrl('blog_show', array('id' => $id)), 301);
            //return $this->redirectToRoute('blog_list');
        }
        $comments = $blog->getComment($comment);
        return $this->render('Blog/show.html.twig', array('blog'=> $blog,'form' => $form->createView(),'comments'=>$comments));
        
    }
    
    /**
     * @Route("/blog/{blogid}/updateComment/{id}", name="comment_update")
     */
    public function updateCommentAction(Request $request,$blogid,$id)
    {
        $comment = $this->getDoctrine()->getRepository(Comment::class)->findOneById($id);
        $comment->setStatus(1);
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        return $this->redirect($this->generateUrl('blog_show', array('id' => $blogid)), 301);
    }
    
    /**
     * @Route("/blog/edit/{id}", name="blog_update")
     */
    public function updateAction(Request $request,$id)
    {
        $blog = $this->getDoctrine()->getRepository(Blog::class)->findOneById($id);
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirect($this->generateUrl('blog_show', array('id' => $id)), 301);
        }
        return $this->render('Blog/edit.html.twig', array('blog'=> $blog,'form' => $form->createView()));
    }
    
    /**
     * @Route("/blog/{blogid}/deleteComment/{id}", name="comment_delete")
     */
    public function deleteCommentAction(Request $request,$blogid,$id)
    {
        $comment = $this->getDoctrine()->getRepository(Comment::class)->findOneById($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($comment);
        $em->flush();
        return $this->redirect($this->generateUrl('blog_show', array('id' => $blogid)), 301);
    }

}
