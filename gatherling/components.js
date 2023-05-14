const store = new Vuex.Store({
    state: {
        events: []
    },
    mutations: {
        setEvents (state, events) {
            state.events = events;
        }
    }
});

Vue.use(Toasted);

// Vue.component('activeevents', {
//     template: `
//     <div>
//     <table class="gatherling_full">
//     <tr><td colspan="12"><b>ACTIVE EVENTS</b></td></tr>
//     </table>
//     <ActiveEvent v-for="event in $data.events" v-bind:key="event.id" v-bind:name="event.name">i</ActiveEvent>
//     </div>
//     `
// });

//   Vue.component('ActiveEvent', {
//     props: ['id', 'name'],
//     template: '<tr><td>Event: {{id}}</td></tr>'
//   });

  Vue.component('nametag', {
    props: ['id', 'name', 'mtgo_username', 'discord_handle', 'client', 'email', 'display_name'],
    template: '<a href=\"profile.php?player={{ name }}\">{{ name }}</a>'
  });

  var app = new Vue({
    el: '#maincontainer',
    store
  })
