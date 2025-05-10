<?php

namespace App\Tests\Controller;

use App\Entity\Course;
use App\Entity\Lesson;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LessonControllerTest extends WebTestCase
{
    protected static function getManager() : EntityManagerInterface
    {
        $container = static::getContainer();
        /** @var EntityManagerInterface $manager */
        return $container->get(EntityManagerInterface::class);
    }

    public static function getLessons() : array
    {
        return self::getManager()->getRepository(Lesson::class)->findAll();
    }

    public function testLessonPageStatus(): void
    {
        $client = static::createClient();

        $lessons = self::getLessons();
        /** @var Lesson $lesson */
        $lesson = reset($lessons);

        $urls = [
            '/lessons',
            '/lessons/new',
            "/lessons/{$lesson->getId()}",
            "/lessons/{$lesson->getId()}/edit",
        ];

        foreach ($urls as $url) {
            $crawler = $client->request('GET', $url);
            self::assertResponseIsSuccessful();
        }
    }

    public function testLessonNotFound(): void
    {
        $client = static::createClient();

        $url = '/lessons/99999';

        $crawler = $client->request('GET', $url);

        self::assertResponseStatusCodeSame(404);
    }

    public function testLessonsCount(): void
    {
        $client = static::createClient();
        $url = '/lessons';

        $crawler = $client->request('GET', $url);

        self::assertResponseIsSuccessful();

        $lessons = self::getLessons();

        self::assertEquals(count($lessons), $crawler->filter('.card')->count());
    }

    public function testLessonIsAvailable(): void
    {
        $client = static::createClient();

        $url = '/lessons/new';

        $crawler = $client->request('GET', $url);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form[name=lesson]');
    }

    public function testLessonCreate() : void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/lessons');

        self::assertResponseIsSuccessful();

        $link = $crawler->selectLink('Добавить урок')->link();
        $crawler = $client->click($link);

        self::assertResponseIsSuccessful();

        $courses = CourseControllerTest::getCourses();
        /** @var Course $course */
        $course = reset($courses);

        $form = $crawler->selectButton('Сохранить')->form([
            'lesson[name]' => 'Новый тестовый урок',
            'lesson[content]' => 'Тестовый урок',
            'lesson[orderNumber]' => 111,
            'lesson[course]' => $course->getId(),
        ]);

        $client->submit($form);
        $client->followRedirect();

        self::assertSelectorTextContains('.row', 'Новый тестовый урок');
    }

    public function testLessonCreateByCourseId() : void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/courses');

        self::assertResponseIsSuccessful();

        $link = $crawler->selectLink('Пройти')->link();
        $crawler = $client->click($link);

        self::assertResponseIsSuccessful();

        $link = $crawler->selectLink('Добавить урок')->link();
        $crawler = $client->click($link);

        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Сохранить')->form([
            'lesson[name]' => 'Новый урок',
            'lesson[content]' => 'Новый урок',
            'lesson[orderNumber]' => 111,
        ]);

        $client->submit($form);
        $client->followRedirect();

        self::assertSelectorTextContains('.row', 'Новый урок');
    }

    public function testLessonEdit() : void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/lessons');

        self::assertResponseIsSuccessful();

        $link = $crawler->selectLink('Подробней')->link();
        $crawler = $client->click($link);

        self::assertResponseIsSuccessful();

        $link = $crawler->selectLink('Редактировать')->link();
        $crawler = $client->click($link);

        $url = $client->getRequest()->getPathInfo();
        preg_match('#/lessons/(\d+)/edit#', $url, $matches);
        $lessonId = $matches[1] ?? null;

        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Сохранить')->form([
            'lesson[name]' => 'New name',
        ]);

        $client->submit($form);

        self::assertResponseRedirects("/lessons/$lessonId");
        $client->followRedirect();

        self::assertSelectorTextContains('.row', 'New name');
    }

    public function testLessonDelete() : void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/lessons');

        self::assertResponseIsSuccessful();

        $link = $crawler->selectLink('Подробней')->link();
        $crawler = $client->click($link);

        $url = $client->getRequest()->getPathInfo();
        preg_match('#/lessons/(\d+)#', $url, $matches);
        $lessonId = $matches[1] ?? null;

        self::assertNotNull($lessonId);
        self::assertResponseIsSuccessful();

        $lesson = self::getManager()->getRepository(Lesson::class)->find($lessonId);
        self::assertNotNull($lesson);

        $form = $crawler->selectButton('Удалить')->form();
        $client->submit($form);

        self::assertResponseRedirects("/courses/{$lesson->getCourse()->getId()}");
        $client->followRedirect();

        $lesson = self::getManager()->getRepository(Lesson::class)->find($lesson->getId());
        self::assertNull($lesson, 'Урок не был удалён');
    }
}
