<template>
    <v-card style="margin-top: 52px;">
    <v-layout>
      <!-- <v-system-bar color="deep-purple darken-3"></v-system-bar> -->
<!-- 
      <v-app-bar

        prominent
      >
        <v-app-bar-nav-icon variant="text" @click.stop="drawer = !drawer"></v-app-bar-nav-icon>

        <v-img
          width="20px"
          style="margin:10px"
          src="../../assets/greenavi.png"
          >
          </v-img> 
      </v-app-bar>

      <v-navigation-drawer
        v-model="drawer"
        location="top"
        temporary
      >
      <v-list
      :items="items"
      >
      
      </v-list>
      </v-navigation-drawer> -->

      <v-main style="height: 500px;">
        <v-card-text>
          1
        </v-card-text>
      </v-main>
    </v-layout>
  </v-card>
</template>
<script>
  export default {
    data: () => ({
      drawer: false,
      group: null,
      items: [
        {
          title: 'Foo',
          value: 'foo',
        },
        {
          title: 'Bar',
          value: 'bar',
        },
        {
          title: 'Fizz',
          value: 'fizz',
        },
        {
          title: 'Buzz',
          value: 'buzz',
        },
      ],
    }),

    watch: {
      group () {
        this.drawer = false
      },
    },
  }
</script>