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
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $admin_user = $this->getDummyUserEntity($manager, "root", true);
        $normal_user_1 = $this->getDummyUserEntity($manager, "user1");
        $normal_user_2 = $this->getDummyUserEntity($manager, "user2");

        $banned_user_3 = $this->getDummyUserEntity($manager, "user3");
        $banned_user_3->setBannedTill(new DateTime("2030-09-11"));
        $banned_user_3->setBanReason("I'm a twat");

        $persistence_array = [$admin_user, $normal_user_1, $normal_user_2, $banned_user_3];

        $quiz1 = $this->getQuizWithQuestionsAnswers($manager, $admin_user);
        $quiz1[0]->setTitle("Test Admin Quiz!");

        array_unshift($quiz1, $this->getDummyMediaObjectEntity($admin_user, true));
        $quiz1[1]->setPhoto($quiz1[0]);

        $quiz2 = $this->getQuizWithQuestionsAnswers($manager, $normal_user_1);
        $quiz2[0]->setTitle("Test User1 Quiz!");
        array_unshift($quiz2, $this->getDummyMediaObjectEntity($normal_user_1));
        $quiz2[1]->setPhoto($quiz2[0]);

        $quiz3 = $this->getQuizWithQuestionsAnswers($manager, $normal_user_2);
        $quiz3[0]->setTitle("Test User2 Quiz!");
        array_unshift($quiz3, $this->getDummyMediaObjectEntity($normal_user_2));
        $quiz3[1]->setPhoto($quiz3[0]);

        array_push($quiz2, $this->getDummyReportEntity($manager, $quiz2[1], $normal_user_2));

        $this->persistArray($manager, $persistence_array);
        $this->persistArray($manager, $quiz1);
        $this->persistArray($manager, $quiz2);
        $this->persistArray($manager, $quiz3);
    }

    private function persistArray(ObjectManager $manager, array $entity_array) : void
    {
      foreach ($entity_array as $entity)
      {
        $manager->persist($entity);
      }

      $manager->flush();
    }

    /**
     * Adds a dummy quizz with one question and two answers to the database.
     * @param  ObjectManager $manager Doctrine Entity Manager.
     * @param  User          $user    User Entity.
     */
    public function getQuizWithQuestionsAnswers(ObjectManager $manager, User $user) : Array
    {
      $quiz = $this->getDummyQuizEntity($manager, $user);
      $question = $this->getDummyQuestionOfTypeZeroEntity($manager, $quiz);
      $answer1 = $this->getDummyAnswerEntity($manager, $question, true);
      $answer2 = $this->getDummyAnswerEntity($manager, $question, false);

      return [$quiz, $question, $answer1, $answer2];
    }

    /**
     * Returns an example MediaObject that is a PNG image.
     * @param  User        $user         User Entity.
     * @param  bool        $reset_folder    Will delete media folder from public is true.
     * @return MediaObject MediaObject Entity.
     */
    public function getDummyMediaObjectEntity(User $user, bool $reset_folder = false) : MediaObject
    {
      $fs = new Filesystem();
      $media = new MediaObject();
      $src = __DIR__."\..\..\public";
      $photoSrcFolder = $src."\\fixture\\";
      $mediaFolder = $src."\\media";
      $random_number = rand(1,9999);
      $new_file_name = "kitty".$random_number."PNG";
      //Remove the media folder we don't need the old photos

      if($reset_folder) {
        $fs->remove($mediaFolder);
      }

      //Make a copy of the orginal because the UploadedFile will remove it
      $fs->copy($photoSrcFolder."\\kitty.PNG", $photoSrcFolder.$new_file_name, true);
      $uploadedFile = new UploadedFile(
        $photoSrcFolder.$new_file_name,
        "kitty.PNG",
        "image\png",
        filesize($photoSrcFolder.$new_file_name),
        null,
        true
      );
      $media->setUser($user);
      $media->file = $uploadedFile;

      return $media;
    }

    /**
     * Returns an example User entity with Admin privilages (ROLE_ADMIN).
     * @param  ObjectManager $manager  Doctrine Entity Manager.
     * @param  string        $username The username.
     * @param  bool       $is_admin True if should have admin privilage.
     * @return User                    The User entity.
     */
    public function getDummyUserEntity(ObjectManager $manager, string $username, bool $is_admin = false) : User
    {
        $user = new User();
        $user->setUsername($username);

        if($is_admin) {
          $user->setRoles(["ROLE_ADMIN"]);
        }

        $user->setEmail("user_".$username."@user.pl");
        $user->setPassword($this->encoder->encodePassword($user, "root"));

        return $user;
    }

    /**
     * Returns an example Quiz Entity.
     * @param  ObjectManager $manager Doctrine Entity Manager.
     * @param  User          $user    User Entity.
     * @param  MediaObject|null   $media   A MediaObject or null
     * @return Quiz                   Quiz Entity.
     */
    public function getDummyQuizEntity(ObjectManager $manager, User $user, ?MediaObject $media = null) : Quiz
    {
        $quiz = new Quiz();
        $quiz->setUser($user);
        $quiz->setTags("#class, #test, #tag");
        $quiz->setType(0);
        $quiz->setTitle("Test Quiz!");

        if(!is_null($media)) {
          $quiz->setPhoto($media);
        }

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
