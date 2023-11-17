import AltAkismet from './components/AltAkismet.vue';

Statamic.booting(() => {
    Statamic.$components.register('alt-akismet', AltAkismet);
});
