<script setup lang="ts">
import type { NavigationMenuViewportProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { reactiveOmit } from "@vueuse/core"
import {
  NavigationMenuViewport,

  useForwardProps,
} from "reka-ui"
import { cn } from "@/lib/utils"

const props = defineProps<NavigationMenuViewportProps & { class?: HTMLAttributes["class"] }>()

const delegatedProps = reactiveOmit(props, "class")

const forwardedProps = useForwardProps(delegatedProps)
</script>

<template>
  <div class="tw-absolute tw-left-0 tw-top-full tw-flex tw-justify-center">
    <NavigationMenuViewport
      v-bind="forwardedProps"
      :class="
        cn(
          'tw-origin-top-center tw-relative tw-mt-1.5 tw-h-[--reka-navigation-menu-viewport-height] tw-w-full tw-overflow-hidden tw-rounded-md tw-border tw-border-slate-200 tw-bg-white tw-text-slate-950 tw-shadow data-[state=open]:tw-animate-in data-[state=closed]:tw-animate-out data-[state=closed]:tw-zoom-out-95 data-[state=open]:tw-zoom-in-90 md:tw-w-[--reka-navigation-menu-viewport-width] tw-left-[var(--reka-navigation-menu-viewport-left)] dark:tw-border-slate-800 dark:tw-bg-slate-950 dark:tw-text-slate-50',
          props.class,
        )
      "
    />
  </div>
</template>
