## Overview

If I sent you the link to this repository, I think your resume looks solid, and I'm excited to see if you're the developer I'm looking for to help me bring several projects from idea to product. Thank you for taking this on. (There's a more detailed reminder about this opportunity in the last section.)

For this test project, I'm asking you to implement a basic mail-merge tool with a twist. This functionality is related to one of the apps we would be working on together, but I'm not trying to get free work out of you: this project will only be used to evaluate your skills and your fit with what I'm looking for, never for anything else.

There's minimum functionality that your submission must include to be considered and additional features you could take on if you have time and want to show off more of your skills. There is no time limit, and I'm not watching over your shoulder, so there's no pressure and no penalty if you get stuck on something for a bit or have to look up some function. That's normal for a working programmer! I think tests with artificial time pressure, unrealistic work, or an expectation that you're not going to use Google don't give you a chance to show what you can do.

## General expectations for this project

### Submission format

A link to your git repo online, or an entire local repo zipped and sent to me. If you take the ZIP route, be sure to include the hidden .git folder. If you need to rename it to (e.g.) `_git` so it's not hidden, that's ok.

Please make commits with good commit messages along the way, especially if you go beyond the minimum functionality. I'd like to see your work process.

### Option 1: Fork or clone this Laravel project

If you are already setup for local Laravel development, or you don't mind setting it up, fork or clone this repository. This is a fresh [Laravel 7](https://laravel.com/docs/7.x/installation) project made with `laravel new test-project` and nothing changed but the README files. You can install Laravel's dependencies with `composer install` and run the app with `php artisan serve`. Implement the specification below using Laravel views and controller(s) or functions.

### Option 2: Use the [built-in PHP server](https://www.php.net/manual/en/features.commandline.webserver.php)

If you're not currently setup for local Laravel 7 development, create a new git repo and just create the files you need to fulfill as much of the specification as you choose to implement. Be sure to include a README file with the exact `php -S` command necessary to access your app.

## The specification!

### Core functionality

Your user has a CSV file of contacts. The first row is headers. The only column you're sure will be there is "email". Any other columns are up to the user, and the columns may be in any order.

Create a form where the user can provide their CSV file and one *or more* templates. A template consists of a `subject` text input and a `message` textarea. Both fields support variable interpolation in the form `{{ column_header }}`. So a subject like `"Hello, {{ first_name }}!"` would access the column of the CSV file with the header `"first_name"` and output something like `"Hello, John!"`.

When the user submits the form, output an HTML table with columns Subject and Message. Each line in the CSV file (except the header line) should produce one row in the HTML table with the values from that row appropriately interpolated into the template.

What's this about one *or more* templates? Well, probably not every contact in the CSV will have every column filled. So the user needs to be able to create as many *fallback templates* as they want. If a contact in the CSV has a blank value for a field that the first template uses, try the second template instead, on down the list of templates. If you get to the last template and it still uses a value that the contact doesn't inlcude, just use the last template anyway and put blanks for the missing values.

For example, if `message` in the first template was `"Do you think this would be useful for {{ company }}?"`, but the company column for a contact in the CSV file is blank, you would fallback to the next template, which might be `"What do you think of this, {{ first_name }}?"`

Presumably you'll need to use a little JavaScript to handle the "as-many-templates-as-you-want" functionality, but it doesn't need to be fancy. Use a framework if you want, or just use vanilla JavaScript or jQuery.

### Bonus quests

If you want to show off your skills, here are some ways you could take this further. Select any of these in any order, or none, if you don't have time:

* Parse the header line of the CSV file immediately when the user chooses it to upload and provide a visible list of the fields available
  * To go even further, make the field names clickable or drag-and-dropable to conveniently add the field to the template
* Make the UI attractive
* Create automated tests
* Use mailtrap.io and SMTP to actually send email messages in addition to displaying the table (only use very short CSV files with this, since free mailtrap accounts have strict sending rate limits)
* Save the objects to a relational database (preferably use SQLite since it requires minimal setup).
  * A `Campaign` has many ordered `Templates`
  * CSV rows become `Contacts`—remember that you don't know what columns the CSV will have except email!
  * A `Campaign` is related to many `Contacts` through `Deliveries`, which represent sending a particular `Template` with a particular rendered value to a particular `Contact` at a particular time.

## About this opportunity

If I sent you the link to this repository, I think your resume looks solid, and I'm excited to see if you're the developer I'm looking for to help me bring several projects from idea to product. This is not a one-off, short-term gig: we'll have a probationary period of course, but I'm looking for one or two developers to work with long-term.

In case you've lost track of which job this is related to, this was the pitch in my job listing:

> ### If you value:
> 
> * Moving fast and creating things
> * Seeing entrepreneurial projects through from idea to MVP and beyond
> * Flexible work schedule
> * Prompt pay
> * A boss who understands web app development (I've been doing this 20+ years)
> * Being evaluated by the results you produce, not your credentials
> 
> ### And you can deliver:
> 
> * Rapid application development with Laravel and Vue, React, or Svelte (or another modern JavaScript framework you can convince me is a good idea)
> * Tasteful use of a CSS library (e.g., Bootstrap or Tailwind)
> * Independent work from specifications, recorded explanations, and hand-drawn sketches
> * Good questions
> 
> ...then I would love to get to know you and work together. I'm offering consistent, long-term work at least 20 hrs/week. We can start with a project or two to see how it works out.

