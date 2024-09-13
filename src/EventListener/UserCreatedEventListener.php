<?php

namespace App\EventListener;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Psr\Log\LoggerInterface;
use Src\Event\UserCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsEventListener(event: UserCreatedEvent::class, method: 'onUserCreated')]
class UserCreatedEventListener
{
    private MailerInterface $mailer;
    public function __construct(MailerInterface $mailer, private readonly LoggerInterface $logger)
    {
        $this->mailer = $mailer;
    }

   public function onUserCreated(UserCreatedEvent $event): void
   {
       $user = $event->getUser();

       $this->logger->info("Un userCreated event est survenu : {$user->getEmail()}");
       // Send a welcome email
       $email = (new Email())
           ->from('your_email@example.com')
           ->to($user->getEmail())
           ->subject('Bienvenue sur le site de gestion!')
           ->html('Bonjour ' . $user->getUsername() . ',<br>Bienvenue sur notre application!');

       $this->mailer->send($email);
   }
}
