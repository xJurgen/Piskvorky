<?php


namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

class PostPresenter extends Nette\Application\UI\Presenter
{
    /** @var Nette\Database\Context  */
    private $database;

    /**
     * PostPresenter constructor.
     * @param Nette\Database\Context $database
     */
    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }


    /**
     * @param int $postId
     * @throws Nette\Application\BadRequestException
     */
    public function renderShow(int $postId): void{
        $post = $this->database->table('posts')->get($postId);
        if (!$post) {
            $this->error('Post not found');
        }

        $this->template->post = $post;
        $this->template->comments = $post->related('comments')->order('created_at');
    }


    /**
     * @return Form
     */
    protected function createComponentCommentForm(): Form
    {
        $form = new Form; // means Nette\Application\UI\Form

        $form->addText('name', 'Your name:')
            ->setRequired();

        $form->addEmail('email', 'Email:');

        $form->addTextArea('content', 'Comment:')
            ->setRequired();

        $form->addSubmit('send', 'Publish comment');

        $form->onSuccess[] = [$this, 'commentFormSucceeded'];

        return $form;
    }


    /**
     * @param Form $form
     * @param \stdClass $values
     * @throws Nette\Application\AbortException
     */
    public function commentFormSucceeded(Form $form, \stdClass $values): void
    {
        $postId = $this->getParameter('postId');

        $this->database->table('comments')->insert([
            'post_id' => $postId,
            'name' => $values->name,
            'email' => $values->email,
            'content' => $values->content,
        ]);

        $this->flashMessage('Thank you for your comment', 'success');
        $this->redirect('this');
    }


    /*public function renderDefault(): void{
        $this->template->posts = $this->database->table('posts')
            ->order('created_at DESC')
            ->limit(5);
    }*/
}