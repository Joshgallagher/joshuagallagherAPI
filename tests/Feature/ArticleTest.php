<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Article;

class ArticleTest extends TestCase
{
    /**
     * @test
     */
    public function a_collection_of_articles_are_returned_as_json()
    {
        factory(User::class)->create();
        factory(Article::class)->create([
            'title' => 'Lumen',
            'slug' => 'lumen',
        ]);
        factory(Article::class)->create([
            'title' => 'Laravel',
            'slug' => 'laravel',
        ]);
        factory(Article::class)->create([
            'title' => 'Vue.js',
            'slug' => 'vue-js',
        ]);

        $this->json('GET', '/api/articles')
            ->seeJsonStructure([
                'data' => [[
                    'title',
                    'slug',
                    'teaser',
                    'body',
                    'created_at',
                    'updated_at',
                    'user' => [
                        'data' => [
                            'name'
                        ]
                    ]
                ]],
                'meta' => [
                    'pagination' => [
                        'total',
                        'count',
                        'per_page',
                        'current_page',
                        'total_pages',
                        'links' => [],
                    ]
                ]
            ])
            ->seeJson([
                'title' => 'Lumen',
                'slug' => 'Lumen',
                'title' => 'Laravel',
                'slug' => 'laravel',
                'title' => 'Vue.js',
                'slug' => 'vue-js',
            ])
            ->assertResponseStatus(200);
    }

    /**
     * @test
     */
    public function a_single_article_is_returned_as_json()
    {
        $user = factory(User::class)->create();
        $article = factory(Article::class)->create();

        $this->json('GET', "/api/articles/{$article->slug}")
            ->seeJsonStructure([
                'data' => [
                    'title',
                    'slug',
                    'teaser',
                    'body',
                    'created_at',
                    'updated_at',
                    'user' => [
                        'data' => [
                            'name'
                        ]
                    ]
                ]
            ])
            ->seeJson([
                'title' => $article->title,
                'slug' => $article->slug,
                'teaser' => $article->teaser,
                'body' => $article->body,
                'created_at' => $article->created_at,
                'updated_at' => $article->updated_at,
                'name' => $user->getFullName(),
            ])
            ->assertResponseStatus(200);
    }

    /**
     * @test
     */
    public function a_collection_of_articles_are_paginated()
    {
        $user = factory(User::class)->create();
        factory(Article::class, 25)->create();
        $genArticle = factory(Article::class)->create();

        $this->json('GET', '/api/articles?page=6')
            ->seeJsonStructure([
                'data' => [[
                    'title',
                    'slug',
                    'teaser',
                    'body',
                    'created_at',
                    'updated_at',
                    'user' => [
                        'data' => [
                            'name'
                        ]
                    ]
                ]],
                'meta' => [
                    'pagination' => [
                        'total',
                        'count',
                        'per_page',
                        'current_page',
                        'total_pages',
                        'links' => [
                            'previous',
                        ]
                    ]
                ]
            ])
            ->seeJson([
                'title' => $genArticle->title,
                'slug' => $genArticle->slug,
                'teaser' => $genArticle->teaser,
                'body' => $genArticle->body,
                'created_at' => $genArticle->created_at,
                'updated_at' => $genArticle->updated_at,
                'name' => $user->getFullName(),
                'total' => 26,
                'count' => 1,
                'per_page' => 5,
                'current_page' => 6,
                'total_pages' => 6,
                'previous' => 'http://localhost/api/articles?page=5',
            ])
            ->assertResponseStatus(200);
    }
}
