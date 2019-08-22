<?php

namespace App\DataFixtures;

use DateTime;

use App\Entity\Quiz;
use App\Entity\User;
use App\Entity\Answer;
use App\Entity\Report;
use App\Entity\Question;
use App\Entity\MediaObject;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Filesystem\Filesystem;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class DatabaseDummyValuesFixtures extends Fixture
{
    public $media = null;
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = $this->getDummyUserEntity($manager);
        $this->media = $this->getDummyMediaObjectEntity($user);
        $quiz = $this->getDummyQuizEntity($manager, $user);
        $report = $this->getDummyReportEntity($manager, $quiz, $user);
        $question = $this->getDummyQuestionOfTypeZeroEntity($manager, $quiz);
        $answer1 = $this->getDummyAnswerEntity($manager, $question, true);
        $answer2 = $this->getDummyAnswerEntity($manager, $question, false);

        $manager->persist($user);
        $manager->flush();
        
        foreach ([$report, $this->media, $quiz, $question, $answer1, $answer2] as $entity) {
          $manager->persist($entity);
        }

        $manager->flush();
    }

    /**
     * Returns an example MediaObject that is a PNG image.
     * @return MediaObject MediaObject Entity.
     */
    public function getDummyMediaObjectEntity(User $user) : MediaObject
    {
      $fs = new Filesystem();
      $media = new MediaObject();
      $src = __DIR__."\..\..\public";
      $photoSrcFolder = $src."\\fixture";
      $mediaFolder = $src."\\media";
      //Remove the media folder we don't need the old photos
      $fs->remove($mediaFolder);
      //Make a copy of the orginal because the UploadedFile will remove it
      $fs->copy($photoSrcFolder."\\kitty.PNG", $photoSrcFolder."\\kitty1.PNG", true);
      $uploadedFile = new UploadedFile(
        $photoSrcFolder."\\kitty1.PNG",
        "kitty.PNG",
        "image\png",
        filesize($src),
        null,
        true
      );
      $media->setUser($user);
      $media->file = $uploadedFile;

      return $media;
    }

    /**
     * Returns an example User entity with Admin privilages (ROLE_ADMIN).
     * @param  ObjectManager $manager Doctrine Entity Manager.
     * @return User                   User Entity.
     */
    public function getDummyUserEntity(ObjectManager $manager) : User
    {
        $user = new User();
        $user->setUsername("root");
        $user->setRoles(["ROLE_ADMIN"]);
        $user->setPassword($this->encoder->encodePassword($user, "root"));

        return $user;
    }

    /**
     * Returns an example Quiz Entity.
     * @param  ObjectManager $manager Doctrine Entity Manager.
     * @param  User          $user    User Entity.
     * @return Quiz                   Quiz Entity.
     */
    public function getDummyQuizEntity(ObjectManager $manager, User $user) : Quiz
    {
        $quiz = new Quiz();
        $quiz->setUser($user);
        $quiz->setTags("#class, #test, #tag");
        $quiz->setType(0);
        $quiz->setTitle("Test Quiz!");
        $quiz->setPhoto($this->media);
        $quiz->setIsPublic(true);
        $quiz->setUpdateDate(new DateTime());
        $quiz->setDescription("A test quiz from doctrine fixtures.");

        return $quiz;
    }

    /**
     * Returns an example Report Entity.
     * @param  ObjectManager $manager Doctrine Entity Manager.
     * @param  Quiz          $quiz    Quiz Entity.
     * @param  User          $user    User Entity.
     * @return Report                 Report Entity.
     */
    public function getDummyReportEntity(ObjectManager $manager, Quiz $quiz, User $user) : Report
    {
      $report = new Report();
      $report->setQuiz($quiz);
      $report->setUser($user);
      $report->setReason("He said mean things to me UwU.");
      $report->setDescription("Things like: 'you're stupid', 'kys'");

      return $report;
    }

    /**
     * Returns an example Question Entity.
     * @param  ObjectManager $manager Doctrine Entity Manager.
     * @param  Quiz          $quiz    Quiz Entity.
     * @return Question               Question Entity.
     */
    public function getDummyQuestionOfTypeZeroEntity(ObjectManager $manager, Quiz $quiz) : Question
    {
      $question = new Question();
      $question->setQuiz($quiz);
      $question->setText("Is 100kg of feathers lighter than 100 kg of steel ?");

      return $question;
    }

    /**
     * Returns an example Answer entity that is the answer or not.
     * @param  ObjectManager $manager   Doctrine Entity Manager.
     * @param  Question      $question  Question Entity.
     * @param  bool          $is_answer [description]
     * @return Answer                   Answer Entity.
     */
    public function getDummyAnswerEntity(ObjectManager $manager, Question $question, bool $is_answer) : Answer
    {
      $answer = new Answer();
      $answer->setQuestion($question);
      if($is_answer) {
        $answer->setText("Yes");
        $answer->setIsAnswer(true);
      } else {
        $answer->setText("No");
        $answer->setIsAnswer(false);
      }

      return $answer;
    }

 }
