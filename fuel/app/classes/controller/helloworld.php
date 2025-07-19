<?php
class Controller_Helloworld extends Controller
{
    public function action_index()
    {
        return Response::forge(View::forge('helloworld/index'));
    }
}