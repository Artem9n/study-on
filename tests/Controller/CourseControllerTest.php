<?php

namespace App\Tests\Controller;

use App\Entity\Course;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CourseControllerTest extends WebTestCase
{
    protected static function getManager() : EntityManagerInterface
    {
        $container = static::getContainer();
        /** @var EntityManagerInterface $manager */
        return $container->get(EntityManagerInterface::class);
    }

    public static function getCourses() : array
    {
        return self::getManager()->getRepository(Course::class)->findAll();
    }

    public function testCoursesPageStatus(): void
    {
        $client = static::createClient();

        $courses = self::getCourses();
        /** @var Course $course */
        $course = reset($courses);

        $urls = [
            '/courses',
            '/courses/new',
            "/courses/{$course->getId()}",
            "/courses/{$course->getId()}/edit",
        ];

        foreach ($urls as $url) {
            $crawler = $client->request('GET', $url);
            self::assertResponseIsSuccessful();
        }
    }

    public function testCourseNotFound(): void
    {
        $client = static::createClient();

        $url = '/courses/99999';

        $crawler = $client->request('GET', $url);

        self::assertResponseStatusCodeSame(404);
    }

    public function testCourseCount(): void
    {
        $client = static::createClient();
        $url = '/courses';

        $crawler = $client->request('GET', $url);

        self::assertResponseIsSuccessful();

        $courses = self::getCourses();

        self::assertEquals(count($courses), $crawler->filter('.card')->count());
    }

    public function testCourseCreate() : void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/courses');

        self::assertResponseIsSuccessful();

        $link = $crawler->selectLink('Новый курс')->link();
        $crawler = $client->click($link);

        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Сохранить')->form([
            'course[name]' => 'Новый тестовый курс',
            'course[code]' => 'TEST_COURSE',
            'course[description]' => 'Описание курса',
        ]);

        $client->submit($form);

        self::assertResponseRedirects('/courses');
        $client->followRedirect();

        self::assertSelectorTextContains('.row', 'Новый тестовый курс');
    }

    public function testCourseEdit() : void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/courses');

        self::assertResponseIsSuccessful();

        $link = $crawler->selectLink('Пройти')->link();
        $crawler = $client->click($link);

        self::assertResponseIsSuccessful();

        $link = $crawler->selectLink('Редактировать')->link();
        $crawler = $client->click($link);

        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Сохранить')->form([
            'course[name]' => 'Test',
        ]);

        $client->submit($form);

        $course = self::getManager()->getRepository(Course::class)->findOneBy(['name' => 'Test']);

        self::assertResponseRedirects("/courses/{$course?->getId()}");
        $client->followRedirect();

        self::assertSelectorTextContains('.row', 'Test');
    }

    public function testCourseDelete() : void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/courses');

        self::assertResponseIsSuccessful();

        $link = $crawler->selectLink('Пройти')->link();
        $crawler = $client->click($link);

        $url = $client->getRequest()->getPathInfo();
        preg_match('#/courses/(\d+)#', $url, $matches);
        $courseId = $matches[1] ?? null;

        self::assertNotNull($courseId);
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Удалить')->form();
        $client->submit($form);

        self::assertResponseRedirects('/courses');
        $client->followRedirect();

        $course = self::getManager()->getRepository(Course::class)->find($courseId);

        self::assertNull($course);
    }
}
