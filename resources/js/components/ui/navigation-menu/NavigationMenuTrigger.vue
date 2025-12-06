<script setup lang="ts">
import type { NavigationMenuTriggerProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { reactiveOmit } from "@vueuse/core"
import { ChevronDown } from "lucide-vue-next"
import {
  NavigationMenuTrigger,

  useForwardProps,
} from "reka-ui"
import { cn } from "@/lib/utils"
import { navigationMenuTriggerStyle } from "."

const props = defineProps<NavigationMenuTriggerProps & { class?: HTMLAttributes["class"] }>()

const delegatedProps = reactiveOmit(props, "class")

const forwardedProps = useForwardProps(delegatedProps)
</script>

<template>
  <NavigationMenuTrigger
    v-bind="forwardedProps"
    :class="cn(navigationMenuTriggerStyle(), 'tw-group', props.class)"
  >
    <slot />
    <ChevronDown
      class="tw-relative tw-top-px tw-ml-1 tw-h-3 tw-w-3 tw-transition tw-duration-300 group-data-[state=open]:tw-rotate-180"
      aria-hidden="true"
    />
  </NavigationMenuTrigger>
</template>
