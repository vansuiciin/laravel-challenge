<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function list()
    {
        $posts = Post::select('id', 'title', 'description', 'created_at')->get();
        return PostResource::collection($posts);
    }
    
    public function toggleReaction(Request $request)
    {
        try {
            $validatedData = Validator::make($request->all(), [
                'post_id' => 'required|int|exists:posts,id',
                'like' => 'required|boolean'
            ]);
        
            if ($validatedData->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validatedData->errors()
                ], 401);
            }
        

            $post = Post::findOrFail( $request->post_id); // Use findOrFail() for better error handling
            $userId = auth()->id();

            if ($post->user_id == $userId) {
                return response()->json([
                    'status' => 500,
                    'message' => 'You cannot like your own post'
                ]);
            }

            $like = Like::where('post_id', $request->post_id)
                ->where('user_id', $userId)
                ->first();

        if ($like && $like->post_id == $request->post_id && $request->like) {
            return response()->json([
                'status' => 500,
                'message' => 'You already liked this post'
            ]);
        } elseif ($like && $like->post_id == $request->post_id && !$request->like) {
            $like->delete();

            return response()->json([
                'status' => 200,
                'message' => 'You unliked this post successfully'
            ]);
        }

        if ($request->like) {
            Like::create([
                'post_id' =>$request->post_id,
                'user_id' => $userId
            ]);
    
            return response()->json([
                'status' => 200,
                'message' => 'You liked this post successfully'
            ]);
        }
    
        return response()->json([
            'status' => 500,
            'message' => 'Invalid request'
        ]);


        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        
    }

}
