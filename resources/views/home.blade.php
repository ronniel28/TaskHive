@extends('layouts.task-app')

@section('content')
<section class="hero bg-primary text-white text-center py-5">
    <div class="container">
      <h1 class="display-4">Effortlessly Manage Your Tasks</h1>
      <p class="lead">Stay organized and boost productivity with our simple task management app.</p>
      <a href="#" class="btn btn-light btn-lg">Get Started</a>
    </div>
  </section>

  <!-- Features Section -->
  <section class="features py-5">
    <div class="container">
      <h2 class="text-center mb-4">Features</h2>
      <div class="row">
        <div class="col-md-4">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Task Creation</h5>
              <p class="card-text">Easily create and organize tasks with deadlines and priorities.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Task Tracking</h5>
              <p class="card-text">Track your tasks and mark them as complete when done.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Collaborate</h5>
              <p class="card-text">Work with team members to manage tasks and progress together.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Call-to-Action Section -->
  <section class="cta bg-light text-center py-5">
    <div class="container">
      <h2 class="mb-4">Ready to Get Started?</h2>
      <p class="lead mb-4">Sign up today and start organizing your tasks with ease.</p>
      <a href="#" class="btn btn-primary btn-lg">Sign Up Now</a>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-dark text-white text-center py-3">
    <p>&copy; 2024 TaskHive. All rights reserved.</p>
  </footer>

@endsection