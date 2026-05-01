import { useState } from '@wordpress/element';
import { Button, Notice } from '@wordpress/components';

const SaveBar = ({ settings, apiBase, nonce }) => {
	const [saving, setSaving] = useState(false);
	const [success, setSuccess] = useState(false);

	const handleSave = () => {
		setSaving(true);
		fetch(apiBase, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': nonce,
			},
			body: JSON.stringify(settings),
		})
		.then((res) => res.json())
		.then(() => {
			setSaving(false);
			setSuccess(true);
			// setTimeout(() => setSuccess(false), 3000);
		});
	};

	return (
		<div style={{ marginTop: '20px' }}>
			<Button
				isPrimary
				isBusy={saving}
				disabled={saving}
				onClick={handleSave}
			>
				Save Settings
			</Button>

			{success && (
				<Notice status="success" isDismissible={true} onRemove={() => setSuccess(false)}>
					Settings saved successfully!
				</Notice>
			)}
		</div>
	);
};

export default SaveBar;
