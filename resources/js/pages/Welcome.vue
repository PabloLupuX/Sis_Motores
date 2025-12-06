<template>
  <Navbar />
  <Inicio />
  <Services />
  <FaqAccordion />
  <Contacto />
  <Footer />
</template>

<script setup lang="ts">
import Navbar from '@/components/LandingPage/Navbar.vue'
import Inicio from '@/components/LandingPage/Inicio.vue'
import Services from '@/components/LandingPage/Services.vue'
import FaqAccordion from '@/components/LandingPage/FaqAccordion.vue'
import Contacto from '@/components/LandingPage/Contacto.vue'
import Footer from '@/components/LandingPage/Footer.vue'
</script>

<style>
html {
  scroll-behavior: smooth;
}
</style>
