## Site created for Euroguidance UA
#### with Wordpress

# Installation
* clone repo
* insert `.env` file into root or remane `.env.example`
* run `docker compose up`
* import `Euroguidance.sql` via PhpMyAdmin

## Ports
* `localhost:8095` – Wordpress
* `localhost:8096` – PhpMyAdmin

## Custom blocks
* cd `euroguidance\wordpress\wp-content\themes\euroguidance-theme`
* `npm install`
* `npm run start`
Now you can add your blocks into `euroguidance-theme/src`!