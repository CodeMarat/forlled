const optimizationConfig = () => window.filamentData?.imageUploadOptimization ?? {}

const shouldCompressFile = (file) => {
    if (! (file instanceof File)) {
        return false
    }

    if (! file.type.startsWith('image/')) {
        return false
    }

    if (['image/svg+xml', 'image/gif'].includes(file.type)) {
        return false
    }

    return true
}

const getScaledDimensions = (width, height, maxWidth) => {
    if (! maxWidth || width <= maxWidth) {
        return { width, height }
    }

    const ratio = maxWidth / width

    return {
        width: Math.round(width * ratio),
        height: Math.round(height * ratio),
    }
}

const preferredOutputType = (file) => {
    return ['image/jpeg', 'image/png', 'image/webp'].includes(file.type) ? file.type : 'image/jpeg'
}

const loadImageFromFile = (file) =>
    new Promise((resolve, reject) => {
        const objectUrl = URL.createObjectURL(file)
        const image = new Image()

        image.onload = () => {
            URL.revokeObjectURL(objectUrl)
            resolve(image)
        }

        image.onerror = (error) => {
            URL.revokeObjectURL(objectUrl)
            reject(error)
        }

        image.src = objectUrl
    })

const canvasToBlob = (canvas, type, quality) =>
    new Promise((resolve, reject) => {
        canvas.toBlob((blob) => {
            if (! blob) {
                reject(new Error('Failed to convert canvas to blob'))

                return
            }

            resolve(blob)
        }, type, quality)
    })

const compressImageBeforeUpload = async (file) => {
    if (! shouldCompressFile(file)) {
        return file
    }

    const config = optimizationConfig()
    const image = await loadImageFromFile(file)
    const maxWidth = Number(config.clientResizeWidth ?? 2400)
    const quality = Number(config.clientTransformQuality ?? 0.82)
    const { width, height } = getScaledDimensions(image.naturalWidth, image.naturalHeight, maxWidth)

    const canvas = document.createElement('canvas')
    canvas.width = width
    canvas.height = height

    const context = canvas.getContext('2d')

    if (! context) {
        return file
    }

    context.drawImage(image, 0, 0, width, height)

    const preferredType = preferredOutputType(file)
    const compressedBlob = await canvasToBlob(canvas, preferredType, quality)

    return new File(
        [compressedBlob],
        file.name,
        {
            type: file.type,
            lastModified: file.lastModified,
        },
    )
}

const compressFiles = async (files) => {
    const processedFiles = []

    for (const file of files) {
        try {
            processedFiles.push(await compressImageBeforeUpload(file))
        } catch {
            processedFiles.push(file)
        }
    }

    return processedFiles
}

const applyInputCompression = () => {
    const redispatchedInputs = new WeakSet()

    document.addEventListener(
        'change',
        (event) => {
            const input = event.target

            if (! (input instanceof HTMLInputElement) || input.type !== 'file') {
                return
            }

            if (redispatchedInputs.has(input)) {
                redispatchedInputs.delete(input)

                return
            }

            const files = Array.from(input.files ?? [])

            if (! files.some((file) => shouldCompressFile(file))) {
                return
            }

            event.stopImmediatePropagation()

            compressFiles(files)
                .then((processedFiles) => {
                    const transfer = new DataTransfer()

                    processedFiles.forEach((file) => transfer.items.add(file))

                    input.files = transfer.files
                    redispatchedInputs.add(input)
                    input.dispatchEvent(new Event('change', { bubbles: true }))
                })
                .catch(() => {
                    redispatchedInputs.add(input)
                    input.dispatchEvent(new Event('change', { bubbles: true }))
                })
        },
        true,
    )
}

const applyFilePondImageOptions = () => {
    if (! window.FilePond) {
        return false
    }

    const config = optimizationConfig()
    window.FilePond.setOptions({
        imageTransformOutputQuality: Number(config.clientTransformQuality ?? 0.82),
        imageTransformOutputQualityMode: 'always',
    })

    return true
}

if (!applyFilePondImageOptions()) {
    const interval = window.setInterval(() => {
        if (applyFilePondImageOptions()) {
            window.clearInterval(interval)
        }
    }, 50)

    window.setTimeout(() => window.clearInterval(interval), 5000)
}

applyInputCompression()
