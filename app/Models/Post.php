<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Console\ModelMakeCommand;
use Illuminate\Support\Facades\File;
use Spatie\YamlFrontMatter\YamlFrontMatter;

class Post
{
    public $title;

    public $excerpt;

    public $date;

    public $body;

    public $slug;

    public function __construct($title, $excerpt, $date, $body, $slug)
    {
        $this->title = $title;
        $this->excerpt = $excerpt;
        $this->date = $date;
        $this->body = $body;
        $this->slug = $slug;
    }
    
    public static function all()
    {

        return cache()->rememberForever('posts.all', function(){
            $files = File::files(resource_path("posts/"));
            return collect($files)
            ->map(function($file){ 
                $document = YamlFrontMatter::parseFile($file);

                return new Post(
                    $document->title,
                    $document->excerpt,
                    $document->date,
                    $document->body(),
                    $document->slug
                );
            })->sortByDesc("date");

        });        

        /**
         * Above and below code are the same
         * but above one use only Laravel collection method same as classic php array
         */
        // return array_map(function ($file){
        //     $document = YamlFrontMatter::parseFile($file);
        //     return new Post(
        //         $document->title,
        //         $document->excerpt,
        //         $document->date,
        //         $document->body(),
        //         $document->slug
        //     );
        // }
        // , $files);
    }

    public static function find($slug)
    {
        $post = static::all()->firstWhere('slug', $slug);

        return $post;

    }

    public static function findOrFail($slug)
    {
        $post = static::find($slug);

        if(! $post)
        {
            throw new ModelNotFoundException();
        }

        return $post;

    }
}