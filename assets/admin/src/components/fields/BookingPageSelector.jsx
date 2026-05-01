import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { SelectControl, Spinner } from '@wordpress/components';

const BookingPageSelector = ({ value, onChange }) => {
	const pages = useSelect((select) =>
		select(coreStore).getEntityRecords('postType', 'page', {
			per_page: -1,
			orderby: 'title',
			order: 'asc',
			context: 'embed'
		})
	);

	if (!pages) {
		return <Spinner />;
	}

	const options = [
		{ label: 'Select a page', value: 0 },
		...pages.map((page) => ({
			label: page.title.rendered,
			value: page.id,
		})),
	];

	return (
		<SelectControl
			label="Booking Page"
			value={value}
			options={options}
			onChange={(newVal) => onChange(parseInt(newVal))}
		/>
	);
};

export default BookingPageSelector;
