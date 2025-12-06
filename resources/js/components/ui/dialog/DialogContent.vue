<script setup lang="ts">
import type { DialogContentEmits, DialogContentProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { reactiveOmit } from "@vueuse/core"
import { X } from "lucide-vue-next"
import {
  DialogClose,
  DialogContent,

  DialogOverlay,
  DialogPortal,
  useForwardPropsEmits,
} from "reka-ui"
import { cn } from "@/lib/utils"

const props = defineProps<DialogContentProps & { class?: HTMLAttributes["class"] }>()
const emits = defineEmits<DialogContentEmits>()

const delegatedProps = reactiveOmit(props, "class")

const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
  <DialogPortal>
    <DialogOverlay
      class="tw-fixed tw-inset-0 tw-z-50 tw-bg-black/80 data-[state=open]:tw-animate-in data-[state=closed]:tw-animate-out data-[state=closed]:tw-fade-out-0 data-[state=open]:tw-fade-in-0"
    />
    <DialogContent
      v-bind="forwarded"
      :class="
        cn(
          'tw-fixed tw-left-1/2 tw-top-1/2 tw-z-50 tw-grid tw-w-full tw-max-w-lg tw--translate-x-1/2 tw--translate-y-1/2 tw-gap-4 tw-border tw-border-slate-200 tw-bg-white tw-p-6 tw-shadow-lg tw-duration-200 data-[state=open]:tw-animate-in data-[state=closed]:tw-animate-out data-[state=closed]:tw-fade-out-0 data-[state=open]:tw-fade-in-0 data-[state=closed]:tw-zoom-out-95 data-[state=open]:tw-zoom-in-95 data-[state=closed]:tw-slide-out-to-left-1/2 data-[state=closed]:tw-slide-out-to-top-[48%] data-[state=open]:tw-slide-in-from-left-1/2 data-[state=open]:tw-slide-in-from-top-[48%] sm:tw-rounded-lg dark:tw-border-slate-800 dark:tw-bg-slate-950',
          props.class,
        )"
    >
      <slot />

      <DialogClose
        class="tw-absolute tw-right-4 tw-top-4 tw-rounded-sm tw-opacity-70 tw-ring-offset-white tw-transition-opacity hover:tw-opacity-100 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-slate-950 focus:tw-ring-offset-2 disabled:tw-pointer-events-none data-[state=open]:tw-bg-slate-100 data-[state=open]:tw-text-slate-500 dark:tw-ring-offset-slate-950 dark:focus:tw-ring-slate-300 dark:data-[state=open]:tw-bg-slate-800 dark:data-[state=open]:tw-text-slate-400"
      >
        <X class="tw-w-4 tw-h-4" />
        <span class="tw-sr-only">Close</span>
      </DialogClose>
    </DialogContent>
  </DialogPortal>
</template>
