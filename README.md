# Challenger
This is the Git repository for THE CHALLENGER.

## Project Notes
Before we dive deep into the development and/or usage of the tool, let's first clarify what's the end goal.

Challenger will be a tool, where challenges will get published weekly, allowing the team to improve their skill set, general knowledge, explore different fields, or simply have fun coding with the game-like experience.

We'll have different challenges, under different categories like:
- front-end
- back-end
- full stack
- mainly javascript
- algorithms
- AI
- and many others
also, there'll be tags, which will identify what skills will be required or acquired when you finish the challenge. Examples for those would be:
- PHP
- AWS
- vim
- AdOps
- etc

The users will have one active challenge for the category they've picked to mainly participate in, and also have access to other active and past challenges so that they can gain more experience. They can also level up, view other participants' solutions to get more ideas of how to solve a specific problem, vote for their favorite participant, and much more.

## Project Documentation
Yet to be added

### Git Branching
We are using different branches when it comes to building new features. However, you should keep in mind two things:
* `master` branch is the current representation of the production website and our **the default branch** for each project.
* All new features should be branch out from the **master** branch and when you finish the work, you must make sure your `feature/styling/bugfix/hotfix` branches are up to date with master and ready for merge.
* On some occasions you might have a `deployment-XXXX` or `staging-XXXX`, containing a batch of tasks, or something like that as the name of the branch. Make sure you'll share the name of the branch when you update the task(s).

Do not forget to checkout from the `master` branch when you start new feature and properly name the new branch, based on the feature/bugfix/experimental/styling nature of your changes.
Checking out from another branch will be mentioned explicitly in the task description if needed.

When you create/update task, make sure you'll place the URL to the Git branch with your work in `Git Repository/Branch` Asana task field, which makes the work and review way easier for the rest of the team members.


### Localhost Project URL
For the local setup of the backend, the home and site URLs are set to:
- local.challenger.com

You can change to whatever you want, but make sure you'll update all needed tables:
- wp_options

### Dashboard/Admin access
For localhost Dashboard access, you can use `admin/admin` for `username/password`. Don't judge, this is for your localhost :)
If you have WP-CLI, you can use [wp user create](https://wp-cli.org/commands/user/create/) or simply add a new user from your phpMyAdmin and/or your local MySQL and use another set of creditentials.

### Localhost Debugging
You should have enabled the `WP_DEBUG` set to `TRUE` in your `wp-config.php` file. There are a few other useful debugging options which you can enable:
```
/* Debug Config */
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
error_reporting( E_ALL );
ini_set( 'display_errors', 'yes' );

define( 'FS_METHOD', 'direct' ); // Allows you to upload/update themes/plugins/core from your localhost
```

This is the best practice when it comes to localhost work - having the debug turned on. However, there might be some rare cases where the debug notes are so many, that you might need to turn off the debugging for a while. Again, this should be something temporary, as you should have the debugging turned on on your localhost by default.

### Localhost Media
We are using BE Media from Production as stated above. This plugin loads the media files from the production server to our localhost. However, the plugin works with the standard WP media and most of the files and images which comes from different options cannot be loaded. This is a know bug of the project setup and you might have some images missing.

If you need to work with the localhost media, you should disable the plugin and continue with the work. If you, for some reason need to get an archive with production media, ask in the project's Slack channel and sync with the Project Owner.

## Git Repository Structure and ToC
A short explanation of the structure of the Git repository.

#### the Git repository root folder
This is the folder where we have files and folders like `.gitignore`, `.git`, `README.md`.
This also contains root files, like `ads.txt`, `robotx.txt` and everything else which is required.

By default, all WordPress Core files and folders are ignored, so we can keep the Git repository clean and have only the work files.
If the specific project requires something different, note this in the project notes.

#### wp-content
This is the directory which contains all project `plugins`, `themes`, `mu-plugins` and the like.
This is the standard `wp-content` folder with all needed fiels. Of course, thins like cache, uploads, upgrade, etc are added to the `.gitignore` file. We don't need uploads in our Git repository, right?


## Challenger Setup Details
The Git repository contains a few root files and the `wp-content` directory content, so we are going to setup the project following the steps below.

1)  Navigate to your local webserver folder - `www/html`, `htdocs`, etc, based on your OS. The name of the directory is based on your preferences and whatever is going to work best for you.
Make sure the directory is writable. Again, this is based on your localhost setup and personal preferences.

1)  Clone the Git repository with the with the following command:
`git clone git@github.com:DevriX/challenger-backend.git project-name-folder`.

With this command you'll clone the repository a new folder - `project-name-folder`.
This will create a new folder in your localhost folder and now we need to install WordPress Core.

Navingate to the newly created folder and run `wp core download --skip-content --force`.

The final goal is to have all Git repository files into your toor directory, which includes `.git`, `.gitignore`, `wp-content` and everything else, based on the project needs.

Make sure the directory is writable. Again, this is based on your localhost setup and personal preferences.

01) Create an empty database on your localhost database server and set `utf8mb4_general_ci` for the DB collation. Download the database from [Challenger Project Setup, Git Details, Database dump and Media export](https://app.asana.com/0/1201045555239862/1201047922890388/f) and import the downloaded database into the newly created database. This depends on your localhost setup.

01)  Once the database is imported you'll have [http://local.challenger.com/](http://local.challenger.com/) ready to use. Of course that will be valid you have already created virtual hosts configuration files for each subsite of the multisite network ( the steps for doing so are highly OS and setup specific ).

01) Open the localhost URL you've set above and you'll be redirected to the WordPress new site setup screen.
* Select `English` language.
* Click on `Let's Go!` button on the next screen.
* Populate the fields with the database details you've created earlier. For example, you'll need to  add the name of the database for `Database Name`, enter your database user,database password and database host, and select `wp_` for `Table Prefix`.

If everything is set properly, you'll should be redirected to ***Already Installed*** screen and you should be ready with the localhost setup. Congrats!

Go do to the Dashboard > Settings > Permalinks and click on Save button. In that way, you'll generate a new `.htaccess` file, so you'll have working pretty permalinks.
Make sure the directory is writable, so WordPress can generate the new file.

As stated above, We strongly suggest you to enable the `WP_DEBUG` on your localhost. You can check **Localhost Debugging** section for more details.