import http from 'k6/http';
import { sleep } from 'k6';

const urlToTest = __ENV.URL; // URL transmise en cli

export let options = {
  vus: 50000, // nombre d’utilisateurs virtuels concurrents
  iterations: 50000, // nombre de fois que le test doit s’exécuter
};

export default function() {
  http.get(urlToTest);
  sleep(1);
}